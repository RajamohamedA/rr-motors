<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Create the test account only in local and testing environments.
        if (
            app()->environment(['local', 'testing'])
            && ! User::where('email', 'test@example.com')->exists()
        ) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            DefaultUnitSeeder::class,
            AccountSeeder::class,
            WalkInCustomerSeeder::class,
            IndiaLocationSeeder::class,
        ]);
    }
}
