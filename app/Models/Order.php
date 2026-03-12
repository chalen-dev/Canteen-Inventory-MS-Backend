<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_status',
        'total_amount',
        'description',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
