<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    //
    protected $fillable = [
        'trip_id',
        'passenger_id',
        'seats',
        'status'
    ];
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
    public function passenger()
    {
        return $this->belongsTo(Passenger::class);
    }
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
    public function rating()
    {
        return $this->hasOne(Rating::class);
    }
}
