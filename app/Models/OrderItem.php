<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'inventory_id',
        'quantity',
        'amount'
    ];

    protected static function booted()
    {
        // When an order item is created, decrement stock
        static::created(function ($orderItem) {
            DB::transaction(function () use ($orderItem) {
                $log = InventoryLog::lockForUpdate()->find($orderItem->inventory_id);
                if ($log->quantity_in_stock < $orderItem->quantity) {
                    throw new Exception('Insufficient stock');
                }
                $log->decrement('quantity_in_stock', $orderItem->quantity);
            });
        });

        // When an order item is deleted, increment stock back
        static::deleted(function ($orderItem) {
            $orderItem->inventoryLog()->increment('quantity_in_stock', $orderItem->quantity);
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function inventoryLog()
    {
        return $this->belongsTo(InventoryLog::class, 'inventory_id');
    }
}
