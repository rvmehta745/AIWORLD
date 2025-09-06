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
}
