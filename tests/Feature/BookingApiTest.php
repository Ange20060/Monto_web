<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_passenger_can_create_booking_for_available_trip(): void
    {
        $passengerUser = User::factory()->create([
            'phone' => '1000000001',
        ]);
        $passenger = Passenger::create(['user_id' => $passengerUser->id]);

        $driverUser = User::factory()->create([
            'phone' => '1000000002',
        ]);
        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'license_number' => 'LIC-BOOKING-001',
            'license_expiration' => '2035-12-31',
            'phone' => '4500000001',
            'city' => 'Cotonou',
            'verified' => true,
            'available' => true,
            'is_online' => true,
            'rating' => 4.8,
        ]);

        $trip = Trip::create([
            'driver_id' => $driver->id,
            'departure' => 'Cotonou',
            'destination' => 'Abomey-Calavi',
            'departure_time' => now()->addDay(),
            'price' => 1500,
            'available_seats' => 3,
            'status' => 'waiting',
        ]);

        $token = $passengerUser->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/bookings', [
            'trip_id' => $trip->id,
            'seats' => 2,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('bookings', [
            'trip_id' => $trip->id,
            'passenger_id' => $passenger->id,
            'seats' => 2,
            'status' => 'pending',
        ]);

        $trip->refresh();
        $this->assertSame(1, $trip->available_seats);
    }
}
