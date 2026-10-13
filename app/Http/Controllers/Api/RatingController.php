<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RatingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'stars' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $booking = Booking::with(['passenger.user', 'trip.driver'])->findOrFail($request->booking_id);

        if ($booking->passenger?->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez noter que votre propre réservation.',
            ], 403);
        }

        if (Rating::where('booking_id', $booking->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette réservation a déjà reçu une note.',
            ], 409);
        }

        $rating = DB::transaction(function () use ($booking, $request) {
            $rating = Rating::create([
                'booking_id' => $booking->id,
                'stars' => $request->stars,
                'comment' => $request->comment,
            ]);

            $driver = Driver::findOrFail($booking->trip->driver_id);
            $average = Rating::whereHas('booking.trip', function ($query) use ($driver) {
                $query->where('driver_id', $driver->id);
            })->avg('stars');

            $driver->update([
                'rating' => round((float) $average, 2),
            ]);

            return $rating;
        });

        return response()->json([
            'success' => true,
            'message' => 'Note enregistrée.',
            'rating' => $rating,
        ], 201);
    }

    public function driverRatings(Request $request, int $driverId)
    {
        $driver = Driver::findOrFail($driverId);

        $ratings = Rating::with('booking.trip', 'booking.passenger.user')
            ->whereHas('booking.trip', function ($query) use ($driverId) {
                $query->where('driver_id', $driverId);
            })
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'driver_id' => $driver->id,
            'average_rating' => (float) $driver->rating,
            'ratings' => $ratings,
        ]);
    }
}
