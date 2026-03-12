<?php

namespace Database\Seeders;

use App\Enums\Categories;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Categories::cases() as $categoryEnum) {
            Category::firstOrCreate(
                ['name' => $categoryEnum->label()], // avoid duplicates based on name
                ['description' => $categoryEnum->description()]
            );
        }
    }
}
