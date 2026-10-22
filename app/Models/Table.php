<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    //
    protected $primaryKey = 'table_id';

    protected $fillable = [
        'table_num',
        'status',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'table_id', 'table_id');
    }


}
