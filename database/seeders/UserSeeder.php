<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Base testing users
        $users = [
            //POS user (where POS transactions are tied)
            [
                'name' => 'POS',
                'email' => 'admin@gmail.com',
                'password' => '$2y$12$04DGXKypl1wYOY5wZvNelOV1UrwS7ohEiLSlZ/T5a.lIodew0roxq',
                'role' => UserRole::CUSTOMER->value,
                'is_pos' => true,
            ],
            [
                'name' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => '$2y$12$04DGXKypl1wYOY5wZvNelOV1UrwS7ohEiLSlZ/T5a.lIodew0roxq',
                'role' => UserRole::ADMIN->value,
            ],
            [
                'name' => 'cashier',
                'email' => 'cashier@gmail.com',
                'password' => '$2y$12$gQ9EHcdWuuq89hY8XwE05ePIckaFPzn/W4VAWGpm3CG39CTJnh9Bu',
                'role' => UserRole::CASHIER->value,
            ],
            [
                'name' => 'customer',
                'email' => 'customer@gmail.com',
                'password' => '$2y$12$sV9XQdqSqsjYVirD64MUFu7tP3wtPAG5qyhjE7TBcQA2lCO4EkhRW',
                'role' => UserRole::CUSTOMER->value,
            ],
        ];

        // Create additional random customers
        $customerCount = 10; // number of dummy customers to create

        for ($i = 0; $i < $customerCount; $i++) {
            $users[] = [
                'name' => $faker->name(),
                'email' => $faker->unique()->safeEmail(),
                'password' => Hash::make('password'), // default password for all dummy users
                'role' => UserRole::CUSTOMER->value,
            ];
        }

        // Insert all users
        foreach ($users as $user) {
            User::create($user);
        }

        $this->command->info('Users seeded successfully.');
    }
}
