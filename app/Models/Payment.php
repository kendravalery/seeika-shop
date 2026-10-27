<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['order_id', 'gateway', 'payment_method', 'payment_type', 'transaction_id', 'transaction_status', 'fraud_status', 'snap_token', 'response', 'paid_at'];

    protected $casts = ['response' => 'array', 'paid_at' => 'datetime'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
