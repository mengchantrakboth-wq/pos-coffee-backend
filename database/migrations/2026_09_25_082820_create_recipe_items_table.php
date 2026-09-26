<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recipe_items', function (Blueprint $table) {
            $table->id('recipe_item_id');
            $table->foreignId('product_id')->constrained('products', 'product_id')->restrictOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients', 'ingredient_id')->restrictOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->timestamps();
            $table->unique('product_id', 'ingredient_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_items');
    }
};
