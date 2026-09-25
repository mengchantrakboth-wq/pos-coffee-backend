<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //
    protected $primaryKey = 'customer_id';

    protected $fillable = [
        'name',
        'phone',
        'loyalty_points',
    ];

    public function orders(){
        return $this->hasMany(Order::class, 'customer_id', 'customer_id');
    }
}
