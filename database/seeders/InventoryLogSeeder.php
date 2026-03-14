<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\InventoryLog;
use App\Enums\InventoryStatus;
use Faker\Factory;
use Illuminate\Database\Seeder;

class InventoryLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menuItems = MenuItem::all();

        if ($menuItems->isEmpty()) {
            $this->command->info('No menu items found. Skipping inventory logs.');
            return;
        }

        $faker = Factory::create();

        // Define target counts for special statuses
        $lowStockCount = 5;
        $outOfStockCount = 5;
        $expiredCount = 5;

        // Shuffle items to randomly assign statuses
        $items = $menuItems->shuffle();

        $assignedLow = 0;
        $assignedOut = 0;
        $assignedExpired = 0;

        foreach ($items as $item) {
            // Determine which status to assign
            if ($assignedLow < $lowStockCount) {
                // Low stock
                $quantity = round($faker->randomFloat(2, 0.01, 9.99), 2);
                $status = InventoryStatus::LOW_STOCK;
                $available = true;
                $expiryDate = $faker->dateTimeBetween('now', '+180 days')->format('Y-m-d');
                $assignedLow++;
            } elseif ($assignedOut < $outOfStockCount) {
                // Out of stock
                $quantity = 0;
                $status = InventoryStatus::OUT_OF_STOCK;
                $available = false;
                $expiryDate = $faker->dateTimeBetween('now', '+180 days')->format('Y-m-d');
                $assignedOut++;
            } elseif ($assignedExpired < $expiredCount) {
                // Expired (past expiry, unavailable, status OUT_OF_STOCK)
                $quantity = round($faker->randomFloat(2, 1, 50), 2); // still some quantity but expired
                $status = InventoryStatus::OUT_OF_STOCK;
                $available = false;
                $expiryDate = $faker->dateTimeBetween('-180 days', '-1 day')->format('Y-m-d');
                $assignedExpired++;
            } else {
                // In stock (remaining items)
                $quantity = round($faker->randomFloat(2, 10, 100), 2);
                $status = InventoryStatus::IN_STOCK;
                $available = true;
                $expiryDate = $faker->dateTimeBetween('now', '+180 days')->format('Y-m-d');
            }

            // Generate date acquired (sometime in last 30 days)
            $dateAcquired = $faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d');

            InventoryLog::create([
                'item_id' => $item->id,
                'quantity_in_stock' => $quantity,
                'date_acquired' => $dateAcquired,
                'expiry_date' => $expiryDate,
                'inventory_status' => $status,
                'is_available' => $available,
                'description' => $faker->optional(0.3)->sentence(),
            ]);
        }

        $this->command->info('Inventory logs seeded successfully.');
    }
}
