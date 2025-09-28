<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeaturedProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_type_id',
        'start_date',
        'end_date',
        'featured_url',
        'sort_order',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }

    /**
     * The products that belong to the featured product.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'featured_products_products', 'featured_product_id', 'product_id');
    }
} 