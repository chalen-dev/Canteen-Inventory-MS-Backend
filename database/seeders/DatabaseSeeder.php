<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Storage::disk('public')->deleteDirectory('menu_items');

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            MenuItemSeeder::class,
            InventoryLogSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
        ]);

        $this->command->info('Credentials for Testing:');
        $this->command->info('');
        $this->command->info('Admin');
        $this->command->info('username: admin');
        $this->command->info('email: admin@gmail.com');
        $this->command->info('password: admin');
        $this->command->info('');
        $this->command->info('Cashier');
        $this->command->info('');
        $this->command->info('username: cashier');
        $this->command->info('email: cashier@gmail.com');
        $this->command->info('password: cashier');
        $this->command->info('');
        $this->command->info('Customer');
        $this->command->info('username: customer');
        $this->command->info('email: customer@gmail.com');
        $this->command->info('password: customer');
    }
}
