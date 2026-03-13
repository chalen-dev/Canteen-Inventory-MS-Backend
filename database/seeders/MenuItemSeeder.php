<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

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
            ['name' => 'Grilled Chicken', 'category' => 'Meals', 'price' => 85.00, 'code' => 'M001', 'description' => 'Juicy grilled chicken breast with herbs', 'image' => 'grilled_chicken.jpg'],
            ['name' => 'Beef Burger', 'category' => 'Meals', 'price' => 65.00, 'code' => 'M002', 'description' => 'Classic beef burger with lettuce, tomato, and cheese', 'image' => 'beef_burger.jpg'],
            ['name' => 'Vegetable Pasta', 'category' => 'Meals', 'price' => 55.00, 'code' => 'M003', 'description' => 'Pasta tossed with fresh seasonal vegetables', 'image' => 'vegetable_pasta.jpg'],
            ['name' => 'Fish and Chips', 'category' => 'Meals', 'price' => 75.00, 'code' => 'M004', 'description' => 'Crispy battered fish served with fries', 'image' => 'fish_and_chips.jpg'],
            ['name' => 'Caesar Salad', 'category' => 'Meals', 'price' => 65.00, 'code' => 'M005', 'description' => 'Fresh romaine lettuce with Caesar dressing and croutons', 'image' => 'caesar_salad.jpg'],

            // Snacks
            ['name' => 'French Fries', 'category' => 'Snacks', 'price' => 30.00, 'code' => 'S001', 'description' => 'Crispy golden fries with sea salt', 'image' => 'french_fries.jpg'],
            ['name' => 'Chicken Wings', 'category' => 'Snacks', 'price' => 55.00, 'code' => 'S002', 'description' => 'Spicy buffalo wings with ranch dip', 'image' => 'chicken_wings.jpg'],
            ['name' => 'Onion Rings', 'category' => 'Snacks', 'price' => 35.00, 'code' => 'S003', 'description' => 'Crispy battered onion rings', 'image' => 'onion_rings.jpg'],
            ['name' => 'Mozzarella Sticks', 'category' => 'Snacks', 'price' => 45.00, 'code' => 'S004', 'description' => 'Fried mozzarella with marinara sauce', 'image' => 'mozarella_sticks.jpg'],
            ['name' => 'Garlic Bread', 'category' => 'Snacks', 'price' => 25.00, 'code' => 'S005', 'description' => 'Toasted bread with garlic butter', 'image' => 'garlic_bread.jpg'],

            // Beverages
            ['name' => 'Coca-Cola', 'category' => 'Beverages', 'price' => 20.00, 'code' => 'B001', 'description' => 'Ice-cold classic cola', 'image' => 'coca_cola.jpg'],
            ['name' => 'Fresh Orange Juice', 'category' => 'Beverages', 'price' => 35.00, 'code' => 'B002', 'description' => 'Freshly squeezed orange juice', 'image' => 'fresh_orange_juice.jpg'],
            ['name' => 'Iced Tea', 'category' => 'Beverages', 'price' => 25.00, 'code' => 'B003', 'description' => 'Refreshing lemon iced tea', 'image' => 'iced_tea.jpg'],
            ['name' => 'Espresso', 'category' => 'Beverages', 'price' => 30.00, 'code' => 'B004', 'description' => 'Strong shot of Italian espresso', 'image' => 'espresso.jpg'],
            ['name' => 'Mango Smoothie', 'category' => 'Beverages', 'price' => 45.00, 'code' => 'B005', 'description' => 'Creamy mango smoothie with yogurt', 'image' => 'mango_smoothie.jpg'],

            // Desserts
            ['name' => 'Chocolate Lava Cake', 'category' => 'Desserts', 'price' => 55.00, 'code' => 'D001', 'description' => 'Warm chocolate cake with molten center', 'image' => 'chocolate_lava_cake.jpg'],
            ['name' => 'Cheesecake', 'category' => 'Desserts', 'price' => 50.00, 'code' => 'D002', 'description' => 'Creamy New York style cheesecake', 'image' => 'cheesecake.jpg'],
            ['name' => 'Ice Cream Sundae', 'category' => 'Desserts', 'price' => 35.00, 'code' => 'D003', 'description' => 'Vanilla ice cream with chocolate syrup and nuts', 'image' => 'ice_cream_sundae.jpg'],
            ['name' => 'Apple Pie', 'category' => 'Desserts', 'price' => 40.00, 'code' => 'D004', 'description' => 'Classic apple pie with cinnamon', 'image' => 'apple_pie.jpg'],
            ['name' => 'Tiramisu', 'category' => 'Desserts', 'price' => 55.00, 'code' => 'D005', 'description' => 'Italian coffee-flavored dessert', 'image' => 'tiramisu.jpg'],

            // Combos
            ['name' => 'Burger & Fries Combo', 'category' => 'Combos', 'price' => 120.00, 'code' => 'C001', 'description' => 'Classic beef burger with a side of french fries and a soft drink', 'image' => 'burger_and_fries.jpg'],
            ['name' => 'Chicken Meal Deal', 'category' => 'Combos', 'price' => 110.00, 'code' => 'C002', 'description' => 'Grilled chicken with fries, coleslaw, and a beverage', 'image' => 'chicken_meal_deal.jpg'],
            ['name' => 'Family Feast', 'category' => 'Combos', 'price' => 220.00, 'code' => 'C003', 'description' => '2 burgers, 1 large fries, 4 chicken wings, and 4 soft drinks', 'image' => 'family_feast.jpg'],
            ['name' => 'Veggie Lover\'s Combo', 'category' => 'Combos', 'price' => 100.00, 'code' => 'C004', 'description' => 'Vegetable pasta, garlic bread, and a fresh juice', 'image' => 'veggie_lovers_combo.jpg'],
            ['name' => 'Dessert Duo', 'category' => 'Combos', 'price' => 80.00, 'code' => 'C005', 'description' => 'Any two desserts of your choice with 2 coffees or teas', 'image' => 'desert_duo.jpg'],

            ['name' => 'Pork BBQ with Rice', 'category' => 'Meals', 'price' => 60.00, 'code' => 'M006', 'description' => 'Grilled pork skewers served with garlic rice', 'image' => 'pork_bbq_with_rice.jpg'],
            ['name' => 'Chicken Adobo with Rice', 'category' => 'Meals', 'price' => 70.00, 'code' => 'M007', 'description' => 'Tender chicken stewed in soy sauce and vinegar, with rice', 'image' => 'chicken_adobo_with_rice.jpg'],
            ['name' => 'Tapsilog', 'category' => 'Meals', 'price' => 75.00, 'code' => 'M008', 'description' => 'Beef tapa, garlic fried rice, and fried egg', 'image' => 'tapsilog.jpg'],
            ['name' => 'Pancit Canton', 'category' => 'Meals', 'price' => 50.00, 'code' => 'M009', 'description' => 'Stir-fried noodles with vegetables and chicken', 'image' => 'pancit_canton.jpg'],
            ['name' => 'Siomai with Rice', 'category' => 'Meals', 'price' => 55.00, 'code' => 'M010', 'description' => '4 pieces pork siomai served with rice and dipping sauce', 'image' => 'siomai_with_rice.jpg'],

            ['name' => 'Banana Cue', 'category' => 'Snacks', 'price' => 15.00, 'code' => 'S006', 'description' => 'Deep-fried caramelized saba bananas on a stick', 'image' => 'banana_cue.jpg'],
            ['name' => 'Turon', 'category' => 'Snacks', 'price' => 15.00, 'code' => 'S007', 'description' => 'Fried banana lumpia with langka, coated with caramel', 'image' => 'turon.jpg'],
            ['name' => 'Lumpiang Shanghai', 'category' => 'Snacks', 'price' => 25.00, 'code' => 'S008', 'description' => 'Crispy fried spring rolls with ground pork and vegetables, 4 pcs', 'image' => 'lumpiang_shanghai.jpg'],
            ['name' => 'Buko Juice', 'category' => 'Beverages', 'price' => 20.00, 'code' => 'B006', 'description' => 'Fresh coconut water with tender coconut strips', 'image' => 'buko_juice.jpg'],
            ['name' => 'Sago\'t Gulaman', 'category' => 'Beverages', 'price' => 20.00, 'code' => 'B007', 'description' => 'Iced drink with sago pearls and gelatin, brown sugar syrup', 'image' => 'sagot_gulaman.jpg'],
        ];

        // Create menu items
        // Path to your source images
        $sourceDir = database_path('seeders/images');

        foreach ($items as $item) {
            // Find or create the category
            $category = Category::firstOrCreate(['name' => $item['category']]);

            // Handle the image
            $sourcePath = $sourceDir . '/' . $item['image'];
            if (file_exists($sourcePath)) {
                // Generate a unique filename to avoid overwriting
                $filename = uniqid() . '_' . $item['image'];
                // Store the file in the 'public' disk under 'menu_items'
                Storage::disk('public')->putFileAs('menu_items', new \Illuminate\Http\File($sourcePath), $filename);
                $photoPath = 'menu_items/' . $filename;
            } else {
                // No image found – you could set a default placeholder or null
                $photoPath = null;
            }

            // Create the menu item
            MenuItem::create([
                'name' => $item['name'],
                'code' => $item['code'],
                'price' => $item['price'],
                'category_id' => $category->id,
                'description' => $item['description'],
                'photo_path' => $photoPath,
            ]);
        }
    }
}
