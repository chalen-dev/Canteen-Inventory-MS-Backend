<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\InventoryLog;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class OrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Get all orders
        $orders = Order::all();

        if ($orders->isEmpty()) {
            $this->command->info('No orders found. Skipping order items.');
            return;
        }

        // Get all available inventory logs (is_available = true, quantity_in_stock > 0)
        $availableLogs = InventoryLog::with('menuItem')
            ->where('is_available', true)
            ->where('quantity_in_stock', '>', 0)
            ->get();

        if ($availableLogs->isEmpty()) {
            $this->command->warn('No available inventory logs found. Order items will not be created.');
            return;
        }

        foreach ($orders as $order) {
            // Determine number of items for this order (1 to 5)
            $numItems = rand(1, min(5, $availableLogs->count()));

            // Shuffle logs and take a random subset for this order
            $selectedLogs = $availableLogs->shuffle()->take($numItems);

            foreach ($selectedLogs as $log) {
                // Ensure the log has a menu item (should always be true)
                if (!$log->menuItem) {
                    continue;
                }

                // Generate quantity (1 to 3, but not exceeding available stock)
                $maxQuantity = min(3, (int)$log->quantity_in_stock);
                $quantity = rand(1, $maxQuantity);

                // Calculate amount (price * quantity)
                $amount = $quantity * $log->menuItem->price;

                // Create the order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'inventory_id' => $log->id,
                    'quantity' => $quantity,
                    'amount' => $amount,
                ]);
            }
        }

        $this->command->info('Order items seeded successfully.');
    }
}
