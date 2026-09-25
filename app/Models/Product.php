<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $primary = 'product_id';

    protected $fillable = [
        'name',
        'category_id',
        'base_price',
        'image_path',
        'is_active'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function categories(){
        return $this->belongsTo(Categories::class, 'category_id', 'category_id');
    }

    public function variants(){
        return $this->hasMany(ProductVariant::class, 'product_id', 'product_id');
    }

    public function recipeItems()
    {
        return $this->hasMany(RecipeItem::class, 'product_id', 'product_id');
    }
}
