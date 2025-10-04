<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, Sluggable;

    public const PAYMENT_STATUS = [
        'PENDING'        => 'Pending',
        'SUCCESS'        => 'Success',
        'FAILED'         => 'Failed',
        'READY_FOR_REFUND' => 'ReadyForRefund',
    ];

    public const STATUS = [
        'PENDING'             => 'Pending',
        'ONE_TIME_LINK_PENDING' => 'OneTimeLinkPending',
        'ONE_TIME_LINK_USED'    => 'OneTimeLinkUsed',
    ];

    protected $fillable = [
        'product_type_id',
        'one_time_token',
        'is_token_used',
        'name',
        'slug',
        'logo_image',
        'product_image',
        'short_description',
        'long_description',
        'product_url',
        'video_url',
        'seo_text',
        'extra_link1',
        'extra_link2',
        'extra_link3',
        'use_case1',
        'use_case2',
        'use_case3',
        'use_cases',
        'features_and_highlights',
        'additional_info',
        'twitter',
        'facebook',
        'linkedin',
        'telegram',
        'published_at',
        'payment_status',
        'status',
        'sort_order',
        'is_verified',
        'is_gold',
        'is_human_verified',
    ];

    protected $casts = [
        'is_token_used' => 'boolean',
        'is_verified' => 'boolean',
        'is_gold' => 'boolean',
        'is_human_verified' => 'boolean',
        'published_at' => 'datetime',
        'product_image' => 'array', // Cast JSON to array
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'onUpdate' => true
            ]
        ];
    }

    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }

    /**
     * The categories that belong to the product.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'categories_products', 'product_id', 'category_id');
    }

    /**
     * The price types that belong to the product.
     */
    public function priceTypes()
    {
        return $this->belongsToMany(PriceType::class, 'products_price_types', 'product_id', 'price_type_id');
    }

    /**
     * The featured products that belong to the product.
     */
    public function featuredProducts()
    {
        return $this->belongsToMany(FeaturedProduct::class, 'featured_products_products', 'product_id', 'featured_product_id');
    }
}
