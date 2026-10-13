<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
  public function index(Request $request)
  {
    $passenger = Passenger::where('user_id', $request->user()->id)->first();

    if (!$passenger) {
      return response()->json([
        'success' => false,
        'message' => 'Profil passager introuvable.'
      ], 404);
    }

    $booking = Booking::with('trip')
      ->where('passenger_id', $passenger->id)
      ->get();

    return response()->json([
      'success' => true,
      'data' => $booking
    ]);
  }

  /*
  * Reserver un trajet
  */
  public function store(Request $request)
  {
    $request->validate([
        'trip_id' => 'required|exists:trips,id',
        'seats' => 'required|integer|min:1'
    ]);

    $passenger = Passenger::where('user_id', $request->user()->id)->first();

    if (!$passenger) {
        return response()->json([
            'success' => false,
            'message' => 'Profil passager introuvable.'
        ], 404);
    }

    try {
        $booking = DB::transaction(function () use ($request, $passenger) {
            $trip = Trip::lockForUpdate()->findOrFail($request->trip_id);

            if ($trip->status !== 'waiting') {
                abort(400, 'Ce trajet n\'accepte plus de réservations.');
            }

            if ($trip->available_seats < $request->seats) {
                abort(400, 'Plus assez de places disponibles.');
            }

            do {
                $ticketNumber = 'MNT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(8));
            } while (Booking::where('ticket_number', $ticketNumber)->exists());

            $booking = Booking::create([
                'trip_id' => $trip->id,
                'passenger_id' => $passenger->id,
                'seats' => $request->seats,
                'status' => 'pending',
                'ticket_number' => $ticketNumber
            ]);

            $trip->decrement('available_seats', $request->seats);

            return $booking;
        });
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], $e->getCode() ?: 400);
    }

    return response()->json([
        'success' => true,
        'message' => 'Réservation effectuée.',
        'booking' => $booking
    ], 201);
  }

  /*
  * Voir une reservation
  */
  public function show(Request $request, string $id)
  {
    $booking = Booking::with(['trip', 'passenger'])->findOrFail($id);
    $this->authorize('view', $booking);

    return response()->json([
        'success' => true,
        'data' => $booking
    ]);
  }

  public function update(Request $request, string $id)
  {
    $request->validate([
        'status' => 'required|in:accepted,confirmed,cancelled'
    ]);

    $booking = Booking::with('trip')->findOrFail($id);
    $this->authorize('updateStatus', $booking);

    $allowedTransitions = [
        'pending' => ['accepted', 'cancelled'],
        'accepted' => ['confirmed', 'cancelled'],
    ];

    if (!in_array($request->status, $allowedTransitions[$booking->status] ?? [])) {
        return response()->json([
            'success' => false,
            'message' => "Transition de statut invalide ({$booking->status} → {$request->status})."
        ], 400);
    }

    DB::transaction(function () use ($booking, $request) {
        if ($request->status === 'cancelled') {
            $booking->trip->increment('available_seats', $booking->seats);
        }
        $booking->update(['status' => $request->status]);
    });

    return response()->json([
        'success' => true,
        'message' => 'Statut de la réservation mis à jour.',
        'booking' => $booking->fresh()
    ]);
  }

  /* Annuler une reservation */
  public function destroy(Request $request, string $id)
  {
    $booking = Booking::findOrFail($id);
    $this->authorize('cancel', $booking);

    if ($booking->status !== 'pending') {
        return response()->json([
            'success' => false,
            'message' => 'Impossible d\'annuler la réservation.'
        ], 400);
    }

    DB::transaction(function () use ($booking) {
        $trip = Trip::lockForUpdate()->find($booking->trip_id);
        $trip->increment('available_seats', $booking->seats);
        $booking->delete();
    });

    return response()->json([
        'success' => true,
        'message' => 'Réservation annulée.'
    ]);
  }
}
