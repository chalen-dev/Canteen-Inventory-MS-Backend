<?php
// app/Models/Order.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_status',
        'description',
    ];

    protected $appends = ['total_amount'];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restoreStock()
    {
        foreach ($this->orderItems as $item) {
            $item->inventoryLog()->increment('quantity_in_stock', $item->quantity);
        }
    }
    public function getTotalAmountAttribute()
    {
        return $this->orderItems->sum('amount');
    }
}
