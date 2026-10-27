<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $casts = ['price' => 'decimal:2', 'subtotal' => 'decimal:2', 'quantity' => 'decimal:2', 'refunded_quantity' => 'decimal:2'];

    protected $fillable = ['order_id', 'product_variant_id', 'product_name', 'option_name', 'variant_name', 'price', 'quantity', 'subtotal', 'refunded_quantity'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }
}
