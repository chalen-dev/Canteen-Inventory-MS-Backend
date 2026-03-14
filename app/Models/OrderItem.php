<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'inventory_id',
        'quantity',
        'amount'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function inventoryLog()
    {
        return $this->belongsTo(InventoryLog::class, 'inventory_id');
    }
}
