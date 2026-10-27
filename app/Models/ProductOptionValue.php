<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductImage;

class ProductOptionValue extends Model
{
    protected $fillable = ['product_option_id', 'value', 'position', 'image_id'];

    public function option()
    {
        return $this->belongsTo(ProductOption::class, 'product_option_id');
    }

    public function variants()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_option_values');
    }

    public function image()
    {
        return $this->belongsTo(ProductImage::class, 'image_id');
    }
}
