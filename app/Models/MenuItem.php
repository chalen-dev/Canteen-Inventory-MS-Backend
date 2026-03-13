<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'name',
        'code',
        'price',
        'category_id',
        'photo_path',
        'description',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class);
    }
}
