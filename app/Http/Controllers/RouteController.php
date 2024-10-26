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
        public function optimize(Request $request)
        {
            // Valida la entrada
            $validatedData = $request->validate([
                'start.lat' => 'required|numeric',
                'start.lng' => 'required|numeric',
                'end.lat' => 'required|numeric',
                'end.lng' => 'required|numeric',
            ]);
    
            // Obtén los valores de latitud y longitud
            $start_lat = $validatedData['start']['lat'];
            $start_lng = $validatedData['start']['lng'];
            $end_lat = $validatedData['end']['lat'];
            $end_lng = $validatedData['end']['lng'];
    
            // Aquí llamas a la función de predicción que ejecutaste previamente
            $result = predict_route($start_lat, $start_lng, $end_lat, $end_lng); // Asegúrate de que predict_route esté disponible en el contexto
    
            // Devuelve los resultados
            return response()->json($result);
    }
}