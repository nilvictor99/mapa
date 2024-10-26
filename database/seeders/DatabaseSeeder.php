<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Vehicle::create([
            'name' => 'Truck 1',
            'type' => 'truck',
            'fuel_eff //iciency' => 30.5,
            'max_load' => 5000.00,
            'current_location' => 'Warehouse A'
        ]);
        Vehicle::create([
            'name' => 'Van 1',
            'type' => 'van',
            'fuel_efficiency' => 12.5,
            'max_load' => 1500.00,
            'current_location' => 'Distribution Center B'
        ]);

        Vehicle::create([
            'name' => 'Car 1',
            'type' => 'car',
            'fuel_efficiency' => 7.5,
            'max_load' => 500.00,
            'current_location' => 'Office C'
        ]);

        // Create sample route
        Route::create([
            'start_location' => 'Warehouse A',
            'end_location' => 'Distribution Center B',
            'distance' => 150.75,
            'estimated_time' => 120,
            'co2_emissions' => 45.5,
            'fuel_consumption' => 25.5,
            'weather_conditions' => json_encode([
                'temperature' => 22,
                'condition' => 'clear',
                'wind_speed' => 10
            ]),
            'traffic_status' => json_encode([
                'congestion_level' => 'moderate',
                'incidents' => []
            ])
        ]);
    }
}