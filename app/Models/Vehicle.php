<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'fuel_efficiency',
        'max_load',
        'current_location'
    ];

    public function routes()
    {
        return $this->belongsToMany(Route::class);
    }
}