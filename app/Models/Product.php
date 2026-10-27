<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use Searchable;

    protected $fillable = ['user_id', 'collection_id', 'category_id', 'name', 'slug', 'description', 'attributes', 'status', 'availability', 'preorder_release_label', 'sold_count', 'restocked_at'];

    protected $casts = ['attributes' => 'array', 'restocked_at' => 'datetime'];

    public function toSearchableArray(): array
    {
        $category = $this->category;

        $parentCategoryName = $category?->parent_id ? \App\Models\Category::query()->whereKey($category->parent_id)->value('name') : null;

        // Get active variants for price and stock sorting.
        $activeVariants = $this->variants()
            ->where('is_active', true)
            ->get(['price', 'stock', 'sku']);

        $minPrice = $activeVariants->min('price');
        $maxPrice = $activeVariants->max('price');
        $totalStock = $activeVariants->sum('stock');

        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'description' => $this->description,

            // Category search
            'category_id' => $this->category_id === null ? null : (int) $this->category_id,
            'category_name' => $category?->name,
            'parent_category_name' => $parentCategoryName,

            // Collection filter
            'collection_id' => $this->collection_id === null ? null : (int) $this->collection_id,

            // Product status and availability
            'availability' => $this->availability,
            'status' => $this->status,

            // Sorting attributes
            'created_at' => $this->created_at?->timestamp,
            'updated_at' => $this->updated_at?->timestamp,
            'min_price' => $minPrice === null ? null : (float) $minPrice,
            'max_price' => $maxPrice === null ? null : (float) $maxPrice,
            'total_stock' => (int) $totalStock,
            'sold_count' => (int) ($this->sold_count ?? 0),

            // Search by active variant SKUs
            'sku' => $activeVariants->pluck('sku')->filter()->values()->all(),
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        // Primary image is always loaded first,
        // then images are ordered by their gallery position.
        return $this->hasMany(ProductImage::class)->orderByDesc('is_primary')->orderBy('position');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function options()
    {
        return $this->hasMany(ProductOption::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    public function getPreorderClosedAttribute(): bool
    {
        return (bool) ($this->attributes['preorder_closed'] ?? false);
    }

    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }
}
