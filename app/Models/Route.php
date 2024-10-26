<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_location',
        'end_location',
        'distance',
        'estimated_time',
        'co2_emissions',
        'fuel_consumption',
        'weather_conditions',
        'traffic_status'
    ];

    protected $casts = [
        'weather_conditions' => 'array',
        'traffic_status' => 'array',
    ];

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class);
    }
}