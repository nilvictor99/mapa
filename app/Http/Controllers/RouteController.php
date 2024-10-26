<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RouteController extends Controller
{
    protected $routeOptimizer;

    public function __construct(RouteOptimizer $routeOptimizer)
    {
        $this->routeOptimizer = $routeOptimizer;
    }

    public function index()
    {
        return Inertia::render('Routes/Index', [
            'routes' => Route::with('vehicles')->latest()->get()
        ]);
    }

    public function optimize(Request $request)
    {
        $validated = $request->validate([
            'startLocation' => 'required|string',
            'endLocation' => 'required|string',
            'vehicleType' => 'required|string'
        ]);

        $optimizedRoute = $this->routeOptimizer->optimizeRoute(
            $validated['startLocation'],
            $validated['endLocation'],
            ['vehicleType' => $validated['vehicleType']]
        );

        return response()->json($optimizedRoute);
    }
}