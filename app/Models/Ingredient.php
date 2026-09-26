<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    //
    protected $primaryKey = 'ingredient_id';

    protected $fillable = [
        'name',
        'unit',
        'stock_qty',
        'reorder_level',
    ];

    protected $casts = [
        'stock_qty' => 'decimal:2',
        'reorder_level' => 'decimal:2',
    ];

    public function recipeItems()
    {
        return $this->hasMany(RecipeItem::class, 'ingredient_id', 'ingredient_id');
    }
}
