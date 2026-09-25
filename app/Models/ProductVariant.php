<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    //
    protected $primaryKey = 'variant_id';

    protected $fillable = [
        'product_id',
        'size_name',
        'price_modifier',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
