<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'description',
        'photo_path'
    ];

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }
}
