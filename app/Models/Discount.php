<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $primaryKey = 'discount_id';

    protected $fillable = [
        'name',
        'type',
        'value',
    ];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    public function orders()
    {
        return $this->belongsToMany(
            Order::class,
            'order_discounts',
            'discount_id',
            'order_id',
            'discount_id',
            'order_id'
        );
    }
}
