<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOption extends Model
{
    protected $fillable = ['product_id', 'option_name', 'position'];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function values()
    {
        // for the value position
        return $this->hasMany(ProductOptionValue::class)->orderBy('position');
    }
}
