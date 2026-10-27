<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantOptionValue extends Model
{
    protected $fillable = ['product_variant_id', 'product_option_value_id'];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
    public function optionValue()
    {
        return $this->belongsTo(ProductOptionValue::class);
    }
}
