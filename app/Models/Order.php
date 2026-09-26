<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $primaryKey = 'order_id';

    protected $fillable = [
        'customer_id',
        'user_id',
        'table_id',
        'order_date',
        'order_type',
        'order_status',
        'total_amount',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'table_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }


}
