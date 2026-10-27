<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use SoftDeletes;
    protected $fillable = ['product_id', 'user_id', 'rating', 'comment', 'is_approved', 'product_variant_id'];
    protected $casts = ['rating' => 'integer', 'is_approved' => 'boolean'];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function reviewImages()
    {
        return $this->hasMany(ReviewImage::class);
    }
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
