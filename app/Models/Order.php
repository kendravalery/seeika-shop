<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
    protected $fillable = ['user_id', 'receiver_name', 'phone', 'province', 'city', 'district', 'village', 'postal_code', 'full_address', 'address_id', 'status', 'cancelled_by', 'cancel_reason', 'order_number', 'subtotal', 'shipping_cost', 'total', 'payment_status', 'payment_method', 'paid_at', 'processing_at', 'shipped_at', 'delivered_at', 'completed_at', 'cancelled_at'];

    protected $casts = [
        'total' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',

        'processing_at' => 'datetime',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function address()
    {
        return $this->belongsTo(Address::class);
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
