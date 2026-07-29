<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    //
    protected $fillable = [
        'driver_id',
        'departure',
        'destination',
        'price',
        'available_seats',
        'departure_time',
        'status',
    ];
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
