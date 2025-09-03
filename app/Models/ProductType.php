<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductType extends Model
{
    use HasFactory, SoftDeletes, Sluggable;
    protected $fillable = [
        'name',
        'tag_line',
        'configuration',
        'sort_order',
        'status',
    ];
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ]
        ];
    }
    public function categories()
    {
        return $this->hasMany(Category::class);
    }
    public function priceTypes()
    {
        return $this->hasMany(PriceType::class, 'product_type_id');
    }
}
