<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        Customer::create([
            'name' => 'John Doe',
            'phone' => '1234567890',
            'state' => 'CA',
            'city' => 'San Francisco',
            'street_address' => '123 Main St',
            'zip_code' => '94105',
        ]);
    }
}