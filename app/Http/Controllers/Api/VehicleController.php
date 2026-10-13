<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();

        if (! $driver) {
            return response()->json([
                'success' => false,
                'message' => 'Profil chauffeur introuvable.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $driver->vehicle()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'color' => 'required|string|max:100',
            'plate_number' => 'required|string|unique:vehicles,plate_number',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
            'seats' => 'required|integer|min:1|max:20',
        ]);

        $driver = Driver::where('user_id', $request->user()->id)->first();

        if (! $driver) {
            return response()->json([
                'success' => false,
                'message' => 'Profil chauffeur introuvable.',
            ], 404);
        }

        $vehicle = Vehicle::create([
            'driver_id' => $driver->id,
            'brand' => $request->brand,
            'model' => $request->model,
            'color' => $request->color,
            'plate_number' => $request->plate_number,
            'year' => $request->year,
            'seats' => $request->seats,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Véhicule enregistré avec succès.',
            'vehicle' => $vehicle,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'brand' => 'sometimes|string|max:100',
            'model' => 'sometimes|string|max:100',
            'color' => 'sometimes|string|max:100',
            'plate_number' => 'sometimes|string|unique:vehicles,plate_number,' . $id,
            'year' => 'sometimes|integer|min:1900|max:' . date('Y'),
            'seats' => 'sometimes|integer|min:1|max:20',
        ]);

        $driver = Driver::where('user_id', $request->user()->id)->first();
        $vehicle = Vehicle::findOrFail($id);

        if (! $driver || $vehicle->driver_id !== $driver->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez modifier que votre propre véhicule.',
            ], 403);
        }

        $vehicle->update($request->only(['brand', 'model', 'color', 'plate_number', 'year', 'seats']));

        return response()->json([
            'success' => true,
            'message' => 'Véhicule mis à jour.',
            'vehicle' => $vehicle->fresh(),
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();
        $vehicle = Vehicle::findOrFail($id);

        if (! $driver || $vehicle->driver_id !== $driver->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez supprimer que votre propre véhicule.',
            ], 403);
        }

        $vehicle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Véhicule supprimé.',
        ]);
    }
}
