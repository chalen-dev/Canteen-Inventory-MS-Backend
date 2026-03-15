<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use App\Enums\OrderStatus;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Get all regular customers (exclude POS user)
        $customers = User::where('role', 'customer')
            ->where('is_POS', false)
            ->get();

        if ($customers->isEmpty()) {
            $this->command->info('No customer users found. Skipping orders.');
            return;
        }

        $statuses = [
            OrderStatus::PENDING->value,
            OrderStatus::PREPARING->value,
            OrderStatus::READY->value,
            OrderStatus::COMPLETED->value,
            OrderStatus::CANCELLED->value,
        ];

        $ordersToCreate = 250; // we want at least 200

        for ($i = 0; $i < $ordersToCreate; $i++) {
            $customer = $faker->randomElement($customers);
            $status = $faker->randomElement($statuses);
            $createdAt = $faker->dateTimeBetween('-3 months', 'now');

            Order::create([
                'user_id' => $customer->id,
                'order_status' => $status,
                'description' => $faker->optional(0.6)->sentence(),
                'created_at' => $createdAt,
                'updated_at' => $faker->dateTimeBetween($createdAt, 'now'),
            ]);
        }

        $this->command->info("$ordersToCreate orders seeded successfully.");
    }
}
