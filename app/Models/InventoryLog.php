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
        'description'
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }
}
