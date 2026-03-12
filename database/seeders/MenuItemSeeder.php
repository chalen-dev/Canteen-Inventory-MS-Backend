<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all categories keyed by their name for easy lookup
        $categories = Category::all()->keyBy('name');

        // Define an array of menu items
        $items = [
            // Meals
            ['name' => 'Grilled Chicken', 'category' => 'Meals', 'price' => 85.00, 'code' => 'M001', 'description' => 'Juicy grilled chicken breast with herbs'],
            ['name' => 'Beef Burger', 'category' => 'Meals', 'price' => 65.00, 'code' => 'M002', 'description' => 'Classic beef burger with lettuce, tomato, and cheese'],
            ['name' => 'Vegetable Pasta', 'category' => 'Meals', 'price' => 55.00, 'code' => 'M003', 'description' => 'Pasta tossed with fresh seasonal vegetables'],
            ['name' => 'Fish and Chips', 'category' => 'Meals', 'price' => 75.00, 'code' => 'M004', 'description' => 'Crispy battered fish served with fries'],
            ['name' => 'Caesar Salad', 'category' => 'Meals', 'price' => 65.00, 'code' => 'M005', 'description' => 'Fresh romaine lettuce with Caesar dressing and croutons'],

            // Snacks
            ['name' => 'French Fries', 'category' => 'Snacks', 'price' => 30.00, 'code' => 'S001', 'description' => 'Crispy golden fries with sea salt'],
            ['name' => 'Chicken Wings', 'category' => 'Snacks', 'price' => 55.00, 'code' => 'S002', 'description' => 'Spicy buffalo wings with ranch dip'],
            ['name' => 'Onion Rings', 'category' => 'Snacks', 'price' => 35.00, 'code' => 'S003', 'description' => 'Crispy battered onion rings'],
            ['name' => 'Mozzarella Sticks', 'category' => 'Snacks', 'price' => 45.00, 'code' => 'S004', 'description' => 'Fried mozzarella with marinara sauce'],
            ['name' => 'Garlic Bread', 'category' => 'Snacks', 'price' => 25.00, 'code' => 'S005', 'description' => 'Toasted bread with garlic butter'],

            // Beverages
            ['name' => 'Coca-Cola', 'category' => 'Beverages', 'price' => 20.00, 'code' => 'B001', 'description' => 'Ice-cold classic cola'],
            ['name' => 'Fresh Orange Juice', 'category' => 'Beverages', 'price' => 35.00, 'code' => 'B002', 'description' => 'Freshly squeezed orange juice'],
            ['name' => 'Iced Tea', 'category' => 'Beverages', 'price' => 25.00, 'code' => 'B003', 'description' => 'Refreshing lemon iced tea'],
            ['name' => 'Espresso', 'category' => 'Beverages', 'price' => 30.00, 'code' => 'B004', 'description' => 'Strong shot of Italian espresso'],
            ['name' => 'Mango Smoothie', 'category' => 'Beverages', 'price' => 45.00, 'code' => 'B005', 'description' => 'Creamy mango smoothie with yogurt'],

            // Desserts
            ['name' => 'Chocolate Lava Cake', 'category' => 'Desserts', 'price' => 55.00, 'code' => 'D001', 'description' => 'Warm chocolate cake with molten center'],
            ['name' => 'Cheesecake', 'category' => 'Desserts', 'price' => 50.00, 'code' => 'D002', 'description' => 'Creamy New York style cheesecake'],
            ['name' => 'Ice Cream Sundae', 'category' => 'Desserts', 'price' => 35.00, 'code' => 'D003', 'description' => 'Vanilla ice cream with chocolate syrup and nuts'],
            ['name' => 'Apple Pie', 'category' => 'Desserts', 'price' => 40.00, 'code' => 'D004', 'description' => 'Classic apple pie with cinnamon'],
            ['name' => 'Tiramisu', 'category' => 'Desserts', 'price' => 55.00, 'code' => 'D005', 'description' => 'Italian coffee-flavored dessert'],

            // Combos
            ['name' => 'Burger & Fries Combo', 'category' => 'Combos', 'price' => 120.00, 'code' => 'C001', 'description' => 'Classic beef burger with a side of french fries and a soft drink'],
            ['name' => 'Chicken Meal Deal', 'category' => 'Combos', 'price' => 110.00, 'code' => 'C002', 'description' => 'Grilled chicken with fries, coleslaw, and a beverage'],
            ['name' => 'Family Feast', 'category' => 'Combos', 'price' => 220.00, 'code' => 'C003', 'description' => '2 burgers, 1 large fries, 4 chicken wings, and 4 soft drinks'],
            ['name' => 'Veggie Lover\'s Combo', 'category' => 'Combos', 'price' => 100.00, 'code' => 'C004', 'description' => 'Vegetable pasta, garlic bread, and a fresh juice'],
            ['name' => 'Dessert Duo', 'category' => 'Combos', 'price' => 80.00, 'code' => 'C005', 'description' => 'Any two desserts of your choice with 2 coffees or teas'],
        ];

        $filteredItems = [];
        $countPerCategory = [];
        foreach ($items as $item) {
            $cat = $item['category'];
            if (!isset($countPerCategory[$cat])) {
                $countPerCategory[$cat] = 0;
            }
            if ($countPerCategory[$cat] < 4) {
                $filteredItems[] = $item;
                $countPerCategory[$cat]++;
            }
        }

        // Create menu items
        foreach ($filteredItems as $item) {
            $category = $categories[$item['category']] ?? null;
            if (!$category) {
                continue; // Skip if category not found
            }

            MenuItem::firstOrCreate(
                ['code' => $item['code']], // Assume code is unique
                [
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'category_id' => $category->id,
                    'description' => $item['description'],
                ]
            );
        }
    }
}
