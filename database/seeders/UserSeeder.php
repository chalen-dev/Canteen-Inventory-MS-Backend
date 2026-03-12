<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => '$2y$12$04DGXKypl1wYOY5wZvNelOV1UrwS7ohEiLSlZ/T5a.lIodew0roxq'
            ],
            [
                'name' => 'cashier',
                'email' => 'cashier@gmail.com',
                'password' => '$2y$12$gQ9EHcdWuuq89hY8XwE05ePIckaFPzn/W4VAWGpm3CG39CTJnh9Bu'
            ],
            [
                'name' => 'customer',
                'email' => 'customer@gmail.com',
                'password' => '$2y$12$sV9XQdqSqsjYVirD64MUFu7tP3wtPAG5qyhjE7TBcQA2lCO4EkhRW'
            ]
        ];
        foreach ($users as $user)
        {
            User::create($user);
        }
    }
}
