<?php

namespace App\Models;
//

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'path', 'is_primary', 'position'];

    protected $casts = ['is_primary' => 'boolean'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function optionValues(){
        return $this->hasMany(ProductOptionValue::class, 'image_id');
    }

}
