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

    public function categories(){
        return $this->belongsTo(Categories::class, 'category_id', 'category_id');
    }
}
