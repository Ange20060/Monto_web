<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\Vehicle;

 class VehicleController extends Controller
 {

    public function store(Request $request)
    {

        $request->validate([
          'brand'=>'required|string|max:100',
          'model'=>'required|string|max:100',
          'color'=>'required|string|max:100',
          'plate_number'=>'required|string|unique:vehicles,plate_number',
          'year'=>'required|integer|min:1900|max:' .date('Y'),

        ]);
        $driver = Driver::where('user_id',$request->user()->id)->first();

        if(!$driver){
          return response()->json([
            'success'=> false,
            'message'=>'Profil chauffeur introuvable.'
          ],404);
        }
        $vehicle = Vehicle::create([
          'driver_id'=>$driver->id,
          'brand'=>$request->brand,
          'model'=>$request->model,
          'color'=>$request->color,
          'plate_number'=>$request->plate_number,
          'year'=>$request->year,
        ]);
        return response()->json([
          'success'=> true,
          'message'=>'Véhicule enrégistré avec succès.',
          'vehicle'=>$vehicle
        ],210);
    }
 }
