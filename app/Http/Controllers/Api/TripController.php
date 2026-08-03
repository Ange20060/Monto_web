<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trip;
use App\Models\Driver;

class TripController extends Controller
{
    public function store(Request $request)
    {
      $request->validate([
        'departure'=>'required|string|max:255',
        'destination'=>'required|string|max:255',
        'departure_lat'=>'nullable|numeric',
        'departure_lng'=>'nullable|numeric',
        'destination_lat'=>'nullable|numeric',
        'destination_lng'=>'nullable|numeric',
        'price'=>'required|numeric|min:0',
        'available_seats'=>'required|integer|min:1',
      ]);
      $driver = Driver::where('user_id', $request->user()->id)->fisrt();

      if(!$driver){
        return response()->json([
          'response'=>false,
          'message'=>'Profil chauffeur introuvable.'
        ],404);
      }
        $trip = Trip::create([
          'driver_id'=>$driver->id,
          'departure'=>$request->departure,
          'destination'=>$request->destination,
          'departure_lat'=>$request->departure_lat,
          'departure_lng'=>$request->departure_lng,
          'destination_lat'=>$request->destination_lat,
          'destination_lng'=>$request->destination_lng,
          'price'=>$request->price,
          'available_seats'=>$request->available_seats,
          'status'=>'waiting'
        ]);
        return response()->json([
          'success'=> true,
          'trip'=>$trip
        ],201);
    }
}
