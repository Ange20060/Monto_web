<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Booking $booking): bool
    {
        return $booking->passenger?->user_id === $user->id
            || $booking->trip?->driver?->user_id === $user->id;
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $booking->passenger?->user_id === $user->id
            && $booking->status === 'pending';
    }

    public function updateStatus(User $user, Booking $booking): bool
    {
        return $booking->trip?->driver?->user_id === $user->id;
    }
}
