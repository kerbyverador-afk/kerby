<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Primary Admin / Owner Account
        User::factory()->create([
            'name' => 'Owner Admin',
            'email' => 'admin@boardinghouse.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Secondary Admin Account (Housemate)
        User::factory()->create([
            'name' => 'Housemate Admin',
            'email' => 'housemate@boardinghouse.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 3. Sample Tenant Account
        User::factory()->create([
            'name' => 'Juan Tenant',
            'email' => 'tenant@boardinghouse.com',
            'password' => Hash::make('password'),
            'role' => 'tenant',
        ]);
    }
}
