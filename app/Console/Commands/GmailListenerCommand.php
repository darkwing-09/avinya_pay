<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\PaymentVerificationAgent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GmailListenerCommand extends Command
{
    protected $signature = 'gmail:listen {--interval=2 : Check interval in seconds}';
    protected $description = 'Continuously listens for Paytm payment receipt emails in Gmail 24/7';

    private string $host = 'ssl://imap.gmail.com:993';
    private string $user = 'rootlinux09@gmail.com';
    private string $pass = 'scrcgsfhyiydpfmh';
    private array $processedIds = [];

    public function handle(PaymentVerificationAgent $agent): int
    {
        $interval = max(1, (int) $this->option('interval'));
        $this->info("[*] Starting Continuous 24/7 Gmail Payment Listener for {$this->user}...");
        Log::info("Continuous Gmail payment listener starting...");

        while (true) {
            $fp = null;
            try {
                $this->info('[' . now()->format('H:i:s') . "] Connecting to Gmail IMAP ({$this->host})...");
                $fp = @stream_socket_client($this->host, $errno, $errstr, 10, STREAM_CLIENT_CONNECT);
                if (!$fp) {
                    throw new \RuntimeException("Socket connection failed: {$errstr} ({$errno})");
                }
                stream_set_timeout($fp, 10);

                // Read greeting
                fgets($fp);

                // Authenticate
                fputs($fp, "A001 LOGIN {$this->user} {$this->pass}\r\n");
                $authRes = '';
                while ($l = fgets($fp)) {
                    $authRes .= $l;
                    if (str_starts_with($l, 'A001 ')) break;
                }
                if (!str_contains($authRes, 'A001 OK')) {
                    throw new \RuntimeException("IMAP login failed: {$authRes}");
                }

                // Select INBOX
                fputs($fp, "A002 SELECT INBOX\r\n");
                while ($l = fgets($fp)) {
                    if (str_starts_with($l, 'A002 ')) break;
                }

                $this->info('[' . now()->format('H:i:s') . "] Connected successfully! Fast polling every {$interval}s...");

                // Polling loop over active IMAP connection
                while (true) {
                    // Search for recent emails from Paytm
                    fputs($fp, "A003 SEARCH FROM \"no-reply@paytm.com\"\r\n");
                    $searchRes = '';
                    while ($l = fgets($fp)) {
                        $searchRes .= $l;
                        if (str_starts_with($l, 'A003 ')) break;
                    }

                    if (preg_match('/\* SEARCH\s+([0-9\s]+)/', $searchRes, $matches)) {
                        $rawIds = array_filter(explode(' ', trim($matches[1])));
                        // Inspect the newest 5 messages
                        $newestIds = array_slice($rawIds, -5);

                        foreach ($newestIds as $mid) {
                            if (isset($this->processedIds[$mid])) {
                                continue;
                            }

                            // Fetch headers and body text
                            fputs($fp, "A004 FETCH {$mid} (BODY[HEADER.FIELDS (SUBJECT FROM DATE)] BODY[TEXT])\r\n");
                            $content = '';
                            while ($l = fgets($fp)) {
                                $content .= $l;
                                if (str_starts_with($l, 'A004 ')) break;
                            }

                            if (!empty($content) && (str_contains($content, 'ORD') || str_contains($content, 'Rs.') || str_contains($content, 'Payment Received'))) {
                                $this->info('[' . now()->format('H:i:s') . "] 🔔 Incoming Paytm payment email detected (ID {$mid})!");
                                Log::info("Gmail listener detected Paytm email ID {$mid}");

                                try {
                                    $signal = $agent->ingest($content, 'gmail', 'no-reply@paytm.com');
                                    $this->info('[' . now()->format('H:i:s') . "] ⚡ Signal ingested #{$signal->id} status={$signal->status}");

                                    // If order matched, notify GoCrazy directly
                                    if ($signal->order_id) {
                                        $this->notifyGoCrazy($signal->order_id);
                                    }
                                } catch (Throwable $ingestErr) {
                                    $this->warn("Signal ingestion error: " . $ingestErr->getMessage());
                                }
                            }

                            $this->processedIds[$mid] = true;
                            // Keep processed array lean
                            if (count($this->processedIds) > 500) {
                                $this->processedIds = array_slice($this->processedIds, -250, null, true);
                            }
                        }
                    }

                    sleep($interval);
                }
            } catch (Throwable $e) {
                $this->warn('[' . now()->format('H:i:s') . "] Connection interrupted: " . $e->getMessage() . ". Reconnecting in 3s...");
                Log::warning("Gmail listener reconnecting: " . $e->getMessage());
                if (is_resource($fp)) {
                    @fclose($fp);
                }
                sleep(3);
            }
        }

        return 0;
    }

    private function notifyGoCrazy(string $orderId): void
    {
        try {
            $url = "https://gocrazzyy.vercel.app/api/payments/status/{$orderId}";
            $res = Http::timeout(5)->get($url);
            $this->info('[' . now()->format('H:i:s') . "] 🚀 GoCrazy notified for {$orderId}: status={$res->status()}");
        } catch (Throwable $e) {
            // Non-critical notification attempt
        }
    }
}
