<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create([
        //     'name' => 'John Doe',
        // ]);

        $this->call([
            UserSeeder::class,
            HouseSeeder::class,
            TenantSeeder::class,
            MaintenanceRequestSeeder::class, // Add this line
            // Add other seeders here
        ]);
        // \App\Models\House::factory(5)->create([
        // ]);

        // \App\Models\Tenant::factory(10)->create([
        // ]);

    }
}
