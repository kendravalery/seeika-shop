<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['user_id', 'product_variant_id', 'quantity', 'is_selected'];

    protected $casts = ['is_selected' => 'boolean', 'quantity' => 'integer'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
