<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    //
    protected $fillable = [
        'user_id',
        'license_number',
        'license_expiration',
        'verified',
        'available',
        'rating',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function vehicule()
    {
        return $this->hasOne(Vehicule::class);
    }
    public function trips()
    {
      return $this->hasMany(Trip::class);
    }
}
