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

        $orders = Order::all();
        if ($orders->isEmpty()) {
            $this->command->info('No orders found. Skipping order items.');
            return;
        }

        // Get available logs (is_available = true, quantity_in_stock > 0)
        $availableLogs = InventoryLog::with('menuItem')
            ->where('is_available', true)
            ->where('quantity_in_stock', '>', 0)
            ->get()
            ->keyBy('id'); // key by id for easy updates

        if ($availableLogs->isEmpty()) {
            $this->command->warn('No available inventory logs found. Order items will not be created.');
            return;
        }

        foreach ($orders as $order) {
            $numItems = rand(1, min(5, $availableLogs->count()));
            if ($numItems === 0) continue;

            // Get random logs from available collection
            $selectedLogs = $availableLogs->random($numItems);

            foreach ($selectedLogs as $log) {
                if (!$log->menuItem) continue;

                $maxQuantity = min(3, $log->quantity_in_stock);
                $quantity = rand(1, $maxQuantity);
                $amount = $quantity * $log->menuItem->price;

                // Create order item – this will trigger model event and decrement stock
                OrderItem::create([
                    'order_id' => $order->id,
                    'inventory_id' => $log->id,
                    'quantity' => $quantity,
                    'amount' => $amount,
                ]);

                // After creation, refresh the log's quantity from database
                $log->refresh();

                // If stock is now zero or becomes unavailable, remove it from the available collection
                if ($log->quantity_in_stock <= 0 || !$log->is_available) {
                    $availableLogs->forget($log->id);
                }
            }
        }

        $this->command->info('Order items seeded successfully.');
    }
}
