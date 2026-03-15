<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\InventoryLog;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Get all orders
        $orders = Order::all();
        if ($orders->isEmpty()) {
            $this->command->info('No orders found. Skipping order items.');
            return;
        }

        // Get all available inventory logs (including those that may become out of stock later)
        // We'll filter as we go to ensure we don't exceed stock.
        $inventoryLogs = InventoryLog::with('menuItem')
            ->where('is_archived', false)
            ->where('quantity_in_stock', '>', 0)
            ->orderBy('expiry_date', 'asc') // FIFO: use soonest expiry first
            ->get()
            ->keyBy('id');

        if ($inventoryLogs->isEmpty()) {
            $this->command->warn('No inventory logs with stock found. Order items will not be created.');
            return;
        }

        $totalItemsCreated = 0;

        foreach ($orders as $order) {
            // Determine number of items for this order (1 to 6)
            $numItems = rand(1, 6);
            $itemsAdded = 0;
            $availableLogs = $inventoryLogs->filter(fn($log) => $log->quantity_in_stock > 0);

            if ($availableLogs->isEmpty()) {
                continue; // no stock left for this order
            }

            // Shuffle and take a subset
            $selectedLogs = $availableLogs->shuffle()->take($numItems);

            foreach ($selectedLogs as $log) {
                if (!$log->menuItem) continue;

                // Ensure we have enough stock
                $maxQuantity = min(5, (int)$log->quantity_in_stock);
                if ($maxQuantity <= 0) continue;

                $quantity = rand(1, $maxQuantity);
                $amount = $quantity * $log->menuItem->price;

                // Create the order item – this triggers model event to decrement stock
                OrderItem::create([
                    'order_id' => $order->id,
                    'inventory_id' => $log->id,
                    'quantity' => $quantity,
                    'amount' => $amount,
                ]);

                // Refresh the log to get updated stock from database
                $log->refresh();

                // If stock is now zero or becomes unavailable, remove it from the collection
                if ($log->quantity_in_stock <= 0) {
                    $inventoryLogs->forget($log->id);
                }

                $itemsAdded++;
                $totalItemsCreated++;
            }

            // If we added items, we could recalculate total_amount on order, but model accessor handles it
        }

        $this->command->info("$totalItemsCreated order items seeded successfully.");
    }
}
