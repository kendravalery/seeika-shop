<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $fillable = ['order_item_id', 'user_id', 'quantity', 'amount', 'reason', 'proof', 'status', 'admin_note', 'refunded_at'];

    protected $casts = ['amount' => 'decimal:2', 'quantity' => 'integer', 'refunded_at' => 'datetime'];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
