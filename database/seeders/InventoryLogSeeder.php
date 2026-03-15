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

        // Desired total number of logs with specific statuses (adjust as needed)
        $lowStockCount = 10;
        $outOfStockCount = 10;
        $expiredCount = 10;
        $inStockCount = 30; // remaining will be in stock

        // We'll generate logs for each menu item, up to a maximum per item
        $logsCreated = 0;
        $targetTotal = $lowStockCount + $outOfStockCount + $expiredCount + $inStockCount;

        while ($logsCreated < $targetTotal) {
            foreach ($menuItems->shuffle() as $item) {
                if ($logsCreated >= $targetTotal) break;

                // Determine how many logs for this item (1-3)
                $logsForItem = rand(1, 3);
                for ($i = 0; $i < $logsForItem; $i++) {
                    if ($logsCreated >= $targetTotal) break;

                    // Decide status based on remaining quotas
                    $status = $this->pickStatus($faker, $lowStockCount, $outOfStockCount, $expiredCount, $inStockCount);

                    // Generate values based on status
                    switch ($status) {
                        case InventoryStatus::LOW_STOCK:
                            $quantity = round($faker->randomFloat(2, 0.01, 9.99), 2);
                            $available = true;
                            $expiryDate = $faker->dateTimeBetween('now', '+180 days')->format('Y-m-d');
                            break;
                        case InventoryStatus::OUT_OF_STOCK:
                            $quantity = 0;
                            $available = false;
                            $expiryDate = $faker->dateTimeBetween('now', '+180 days')->format('Y-m-d');
                            break;
                        case InventoryStatus::EXPIRED: // if you have EXPIRED status, otherwise use OUT_OF_STOCK
                            $quantity = round($faker->randomFloat(2, 1, 50), 2);
                            $available = false;
                            $expiryDate = $faker->dateTimeBetween('-180 days', '-1 day')->format('Y-m-d');
                            break;
                        default: // IN_STOCK
                            $quantity = round($faker->randomFloat(2, 10, 100), 2);
                            $available = true;
                            $expiryDate = $faker->dateTimeBetween('now', '+180 days')->format('Y-m-d');
                            break;
                    }

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

                    $logsCreated++;
                }
            }
        }

        $this->command->info("$logsCreated inventory logs seeded successfully.");
    }

    /**
     * Pick a status based on remaining quotas.
     */
    private function pickStatus($faker, &$low, &$out, &$expired, &$inStock): InventoryStatus
    {
        $options = [];
        if ($low > 0) $options[] = InventoryStatus::LOW_STOCK;
        if ($out > 0) $options[] = InventoryStatus::OUT_OF_STOCK;
        if ($expired > 0) $options[] = InventoryStatus::EXPIRED;
        if ($inStock > 0) $options[] = InventoryStatus::IN_STOCK;

        if (empty($options)) {
            return InventoryStatus::IN_STOCK;
        }

        $selected = $faker->randomElement($options);

        // Decrement the corresponding counter
        switch ($selected) {
            case InventoryStatus::LOW_STOCK:    $low--; break;
            case InventoryStatus::OUT_OF_STOCK: $out--; break;
            case InventoryStatus::EXPIRED:      $expired--; break;
            default:                             $inStock--; break;
        }

        return $selected;
    }
}
