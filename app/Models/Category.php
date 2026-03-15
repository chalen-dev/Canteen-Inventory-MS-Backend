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

    protected $appends = ['image_url'];



    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }
}
