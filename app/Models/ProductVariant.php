<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = ['product_id', 'sku', 'price', 'stock', 'weight', 'is_active', 'compare_price', 'max_purchase_per_order', 'preorder_limit'];

    protected $casts = ['price' => 'decimal:2', 'stock' => 'integer', 'weight' => 'decimal:2', 'is_active' => 'boolean'];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function optionValues()
    {
        return $this->belongsToMany(ProductOptionValue::class, 'product_variant_option_values');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
