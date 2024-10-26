<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('start_location');
            $table->string('end_location');
            $table->decimal('distance', 10, 2); // Distance in kilometers
            $table->integer('estimated_time'); // Time in minutes
            $table->decimal('co2_emissions', 8, 2); // CO2 emissions in kg
            $table->decimal('fuel_consumption', 8, 2); // Fuel consumption in liters
            $table->json('weather_conditions'); // Weather data along the route
            $table->json('traffic_status'); // Traffic conditions data
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
