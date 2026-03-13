<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\InventoryLog;
use App\Enums\InventoryStatus;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all menu items
        $menuItems = MenuItem::all();

        if ($menuItems->isEmpty()) {
            $this->command->info('No menu items found. Skipping inventory logs.');
            return;
        }

        // Use faker for realistic data
        $faker = Factory::create();

        foreach ($menuItems as $item) {
            // Generate random quantity (0-100 with two decimals)
            $quantity = round($faker->randomFloat(2, 0, 100), 2);

            // Determine status based on quantity
            $status = match (true) {
                $quantity <= 0 => InventoryStatus::OUT_OF_STOCK,
                $quantity < 10 => InventoryStatus::LOW_STOCK,
                default => InventoryStatus::IN_STOCK,
            };

            // Determine availability (true if stock > 0)
            $available = $quantity > 0;

            // Generate dates
            $dateAcquired = $faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d');
            $expiryDate = $faker->dateTimeBetween('+30 days', '+180 days')->format('Y-m-d');

            // Create the inventory log
            InventoryLog::create([
                'item_id' => $item->id,
                'quantity_in_stock' => $quantity,
                'date_acquired' => $dateAcquired,
                'expiry_date' => $expiryDate,
                'inventory_status' => $status,
                'is_available' => $available,
                'description' => $faker->optional(0.3)->sentence(), // 30% chance of description
            ]);
        }

        $this->command->info('Inventory logs seeded successfully.');
    }
}
