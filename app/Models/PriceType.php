<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_type_id',
        'name',
        'status',
    ];
    public function productType()
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    /**
     * The products that belong to the price type.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'products_price_types', 'price_type_id', 'product_id');
    }
}
