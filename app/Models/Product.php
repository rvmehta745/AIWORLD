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
        'additional_info',
        'twitter',
        'facebook',
        'linkedin',
        'telegram',
        'published_at',
        'payment_status',
        'status',
        'is_verified',
        'is_gold',
        'is_human_verified',
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
}
