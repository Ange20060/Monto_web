<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicule extends Model
{
    //
    protected $fillable = [
        'driver_id',
        'brand',
        'model',
        'color',
        'plate_number',
        'year',
        'seats',
        'verified',
    ];
    public function driver()
    {
      return $this->belongsTo(Driver::class);
    }
}
