<?php

namespace Database\Seeders;

use App\Helpers\ConsoleHyperlink;
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

        $output = <<<TEXT
                Credentials for Testing:

                Cashier

                username: cashier
                email: cashier@gmail.com
                password: cashier

                Customer
                username: customer
                email: customer@gmail.com
                password: customer

                Admin
                username: admin
                email: admin@gmail.com
                password: admin

                Admin Login Address:
                TEXT;

        $this->command->line($output);
        $this->command->line("\033]8;;http://localhost:5173/loginStaff\033\\http://localhost:5173/loginStaff\033]8;;\033\\");
        $this->command->line(" ");
        $this->command->line(" ");
    }
}
