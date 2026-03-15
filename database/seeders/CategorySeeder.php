<?php

namespace Database\Seeders;

use App\Enums\Categories;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Clear the entire 'categories' directory on the public disk
        Storage::disk('public')->deleteDirectory('categories');

        // 2. Define the source directory where the original images are stored
        $sourceDir = database_path('seeders/images/categories');

        // 3. Loop through each category enum
        foreach (Categories::cases() as $categoryEnum) {
            $categoryName = $categoryEnum->label();
            $imageFileName = strtolower($categoryName) . '.jpg';
            $sourcePath = $sourceDir . '/' . $imageFileName;

            // 4. Copy the image to the public disk if it exists
            if (file_exists($sourcePath)) {
                // putFileAs stores the file with the exact name we give it
                Storage::disk('public')->putFileAs(
                    'categories',           // directory inside the public disk
                    new File($sourcePath),  // file to upload
                    $imageFileName          // desired filename
                );
                $photoPath = 'categories/' . $imageFileName;
            } else {
                // Optional: fallback to null or a default placeholder
                $photoPath = null;
            }

            // 5. Create or update the category record
            Category::firstOrCreate(
                ['name' => $categoryName],
                [
                    'description' => $categoryEnum->description(),
                    'photo_path' => $photoPath,
                ]
            );
        }
    }
}
