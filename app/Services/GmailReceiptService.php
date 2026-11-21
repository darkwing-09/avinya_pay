<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class GmailReceiptService
{
    private string $host = 'ssl://imap.gmail.com:993';
    private string $user = 'rootlinux09@gmail.com';
    private string $pass = 'scrcgsfhyiydpfmh';

    public function checkOrder(Order $order): bool
    {
        if ($order->status === Order::PAID) {
            return true;
        }

        // Cache throttle: max once every 4 seconds per order
        $cacheKey = 'gmail_check_' . $order->id;
        if (Cache::has($cacheKey)) {
            return false;
        }
        Cache::put($cacheKey, true, now()->addSeconds(4));

        try {
            return $this->queryGmailForOrder($order);
        } catch (Throwable $e) {
            Log::warning("Gmail receipt check failed for order {$order->order_no}: " . $e->getMessage());
            return false;
        }
    }

    private function queryGmailForOrder(Order $order): bool
    {
        $fp = @stream_socket_client($this->host, $errno, $errstr, 5, STREAM_CLIENT_CONNECT);
        if (!$fp) {
            Log::warning("Gmail IMAP socket error: $errstr");
            return false;
        }
        stream_set_timeout($fp, 5);

        // Read server greeting
        fgets($fp);

        // Login
        fputs($fp, "A001 LOGIN {$this->user} {$this->pass}\r\n");
        $res = '';
        while ($l = fgets($fp)) {
            $res .= $l;
            if (str_starts_with($l, 'A001 ')) break;
        }
        if (!str_contains($res, 'A001 OK')) {
            fclose($fp);
            Log::warning("Gmail IMAP login failed");
            return false;
        }

        // Select INBOX
        fputs($fp, "A002 SELECT INBOX\r\n");
        while ($l = fgets($fp)) {
            if (str_starts_with($l, 'A002 ')) break;
        }

        // Search for order number in email body
        $searchTerms = array_filter([$order->order_no, $order->client_order_id]);
        $foundMsgIds = [];

        foreach ($searchTerms as $term) {
            fputs($fp, "A003 SEARCH BODY \"{$term}\"\r\n");
            $searchRes = '';
            while ($l = fgets($fp)) {
                $searchRes .= $l;
                if (str_starts_with($l, 'A003 ')) break;
            }
            if (preg_match('/\* SEARCH\s+([0-9\s]+)/', $searchRes, $matches)) {
                $ids = array_filter(explode(' ', trim($matches[1])));
                if (!empty($ids)) {
                    $foundMsgIds = array_merge($foundMsgIds, $ids);
                    break;
                }
            }
        }

        if (empty($foundMsgIds)) {
            fputs($fp, "A004 LOGOUT\r\n");
            fclose($fp);
            return false;
        }

        // Fetch the newest matching message
        $msgId = end($foundMsgIds);
        fputs($fp, "A005 FETCH {$msgId} (BODY[HEADER.FIELDS (SUBJECT FROM DATE)] BODY[TEXT])\r\n");
        $msgContent = '';
        while ($l = fgets($fp)) {
            $msgContent .= $l;
            if (str_starts_with($l, 'A005 ')) break;
        }

        fputs($fp, "A006 LOGOUT\r\n");
        fclose($fp);

        if (empty($msgContent)) {
            return false;
        }

        // Feed directly into PaymentVerificationAgent
        $agent = app(PaymentVerificationAgent::class);
        $agent->ingest($msgContent, 'gmail', 'no-reply@paytm.com');

        $order->refresh();
        return $order->status === Order::PAID;
    }
}
