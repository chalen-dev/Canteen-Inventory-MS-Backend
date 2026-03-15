<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    protected $fillable = [
        'item_id',
        'quantity_in_stock',
        'date_acquired',
        'expiry_date',
        'is_available',
        'is_archived',
        'inventory_status',
        'description'
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'item_id');
    }

    public function orderItem()
    {
        return $this->hasMany(OrderItem::class);
    }

    protected static function booted()
    {
        static::saving(function ($inventoryLog) {
            if ($inventoryLog->expiry_date && $inventoryLog->expiry_date < now()->toDateString()) {
                $inventoryLog->is_available = false;
            }
        });
    }
}
