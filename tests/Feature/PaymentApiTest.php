<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Trip;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_passenger_can_pay_for_confirmed_booking(): void
    {
        $passengerUser = User::factory()->create([
            'phone' => '2000000001',
        ]);
        $passenger = Passenger::create(['user_id' => $passengerUser->id]);
        $wallet = Wallet::create([
            'user_id' => $passengerUser->id,
            'balance' => 5000,
        ]);

        $driverUser = User::factory()->create([
            'phone' => '2000000002',
        ]);
        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'license_number' => 'LIC-PAYMENT-001',
            'license_expiration' => '2035-12-31',
            'phone' => '4500000002',
            'city' => 'Porto-Novo',
            'verified' => true,
            'available' => true,
            'is_online' => true,
            'rating' => 4.7,
        ]);

        $trip = Trip::create([
            'driver_id' => $driver->id,
            'departure' => 'Porto-Novo',
            'destination' => 'Cotonou',
            'departure_time' => now()->addDay(),
            'price' => 150,
            'available_seats' => 2,
            'status' => 'waiting',
        ]);

        $booking = Booking::create([
            'trip_id' => $trip->id,
            'passenger_id' => $passenger->id,
            'seats' => 1,
            'status' => 'confirmed',
        ]);

        $token = $passengerUser->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/payments', [
            'booking_id' => $booking->id,
            'method' => 'MTN',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'method' => 'MTN',
            'status' => 'success',
        ]);

        $wallet->refresh();
        $this->assertEquals(4850.0, (float) $wallet->balance);
    }
}
