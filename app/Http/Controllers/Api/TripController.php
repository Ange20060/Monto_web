<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Trip;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $query = Trip::with(['driver.user'])
            ->when($request->filled('departure'), function ($query) use ($request) {
                $query->where('departure', 'like', '%'.$request->departure.'%');
            })
            ->when($request->filled('destination'), function ($query) use ($request) {
                $query->where('destination', 'like', '%'.$request->destination.'%');
            })
            ->when($request->filled('date'), function ($query) use ($request) {
                $query->whereDate('departure_time', $request->date);
            })
            ->when($request->filled('available_seats'), function ($query) use ($request) {
                $query->where('available_seats', '>=', (int) $request->available_seats);
            })
            ->when($request->filled('price_max'), function ($query) use ($request) {
                $query->where('price', '<=', (float) $request->price_max);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            });

        return response()->json([
            'success' => true,
            'data' => $query->latest()->get(),
        ]);
    }

    public function driverTrips(Request $request)
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
            'data' => Trip::with('bookings')->where('driver_id', $driver->id)->latest()->get(),
        ]);
    }

    public function show(Request $request, string $id)
    {
        $trip = Trip::with(['driver.user', 'bookings.passenger.user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $trip,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'departure' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'departure_time' => 'required|date',
            'departure_lat' => 'nullable|numeric',
            'departure_lng' => 'nullable|numeric',
            'destination_lat' => 'nullable|numeric',
            'destination_lng' => 'nullable|numeric',
            'price' => 'required|numeric|min:0',
            'available_seats' => 'required|integer|min:1',
        ]);

        $driver = Driver::where('user_id', $request->user()->id)->first();

        if (! $driver) {
            return response()->json([
                'success' => false,
                'message' => 'Profil chauffeur introuvable.',
            ], 404);
        }

        $trip = Trip::create([
            'driver_id' => $driver->id,
            'departure' => $request->departure,
            'destination' => $request->destination,
            'departure_lat' => $request->departure_lat,
            'departure_lng' => $request->departure_lng,
            'destination_lat' => $request->destination_lat,
            'destination_lng' => $request->destination_lng,
            'price' => $request->price,
            'available_seats' => $request->available_seats,
            'departure_time' => $request->departure_time,
            'status' => 'waiting',
        ]);

        return response()->json([
            'success' => true,
            'trip' => $trip,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'departure' => 'sometimes|string|max:255',
            'destination' => 'sometimes|string|max:255',
            'departure_time' => 'sometimes|date',
            'departure_lat' => 'nullable|numeric',
            'departure_lng' => 'nullable|numeric',
            'destination_lat' => 'nullable|numeric',
            'destination_lng' => 'nullable|numeric',
            'price' => 'sometimes|numeric|min:0',
            'available_seats' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:waiting,started,completed,cancelled',
        ]);

        $driver = Driver::where('user_id', $request->user()->id)->first();
        $trip = Trip::findOrFail($id);

        if (! $driver || $trip->driver_id !== $driver->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez modifier que vos propres trajets.',
            ], 403);
        }

        $trip->update($request->only([
            'departure',
            'destination',
            'departure_time',
            'departure_lat',
            'departure_lng',
            'destination_lat',
            'destination_lng',
            'price',
            'available_seats',
            'status',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Trajet mis à jour.',
            'trip' => $trip->fresh(),
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();
        $trip = Trip::findOrFail($id);

        if (! $driver || $trip->driver_id !== $driver->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez supprimer que vos propres trajets.',
            ], 403);
        }

        $trip->delete();

        return response()->json([
            'success' => true,
            'message' => 'Trajet supprimé.',
        ]);
    }
}

