<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function history(Request $request)
    {
        $wallet = Wallet::firstOrCreate(['user_id' => $request->user()->id]);

        return response()->json([
            'success' => true,
            'wallet' => $wallet,
            'transactions' => $wallet->transactions()->latest()->get(),
            'payments' => Payment::whereHas('booking.passenger', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })->with('booking.trip')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'method' => 'required|in:MTN,MOOV,Celtiis',
        ]);

        $booking = Booking::with(['trip', 'passenger.user'])->findOrFail($request->booking_id);

        if ($booking->passenger?->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez payer que votre propre réservation.',
            ], 403);
        }

        if ($booking->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'La réservation doit être confirmée avant le paiement.',
            ], 400);
        }

        $amount = (float) ($booking->trip->price * $booking->seats);
        $wallet = Wallet::firstOrCreate(['user_id' => $request->user()->id]);

        if ((float) $wallet->balance < $amount) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant.',
            ], 400);
        }

        $payment = DB::transaction(function () use ($booking, $wallet, $request, $amount) {
            $wallet->decrement('balance', $amount);

            $payment = Payment::create([
                'booking_id' => $booking->id,
                'amount' => $amount,
                'method' => $request->method,
                'status' => 'success',
            ]);

            $wallet->transactions()->create([
                'amount' => $amount,
                'type' => 'payment',
            ]);

            return $payment;
        });

        return response()->json([
            'success' => true,
            'message' => 'Paiement effectué avec succès.',
            'payment' => $payment->fresh(),
            'wallet' => $wallet->fresh(),
        ], 201);
    }
}
