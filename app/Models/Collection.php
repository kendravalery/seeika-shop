<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Collection extends Model
{
    use Searchable;

    protected $fillable = ['name', 'slug', 'description', 'thumbnail', 'banner', 'type', 'status'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'status' => $this->status,
        ];
    }
}
