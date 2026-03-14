<?php
// database/seeders/OrderSeeder.php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $customers = User::where('role', 'customer')->get();

        if ($customers->isEmpty()) {
            $this->command->info('No customer users found. Skipping orders.');
            return;
        }

        foreach ($customers as $customer) {
            $numOrders = rand(2, 5);

            for ($i = 0; $i < $numOrders; $i++) {
                $status = $faker->randomElement(OrderStatus::cases())->value;

                Order::create([
                    'user_id' => $customer->id,
                    'order_status' => $status,
                    'description' => $faker->optional(0.7)->sentence(),
                    'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Orders seeded successfully.');
    }
}
