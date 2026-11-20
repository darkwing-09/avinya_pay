<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSignal extends Model
{
    protected $fillable = [
        'source',
        'sender',
        'raw_text',
        'amount',
        'utr',
        'order_id',
        'payer_vpa',
        'matched_order_id',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function matchedOrder()
    {
        return $this->belongsTo(Order::class, 'matched_order_id');
    }
}
