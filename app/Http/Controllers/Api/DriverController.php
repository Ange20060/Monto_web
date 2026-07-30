<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function profile(Request $request)
    {
      $driver  = Driver::where('user_id',$request->user()->id)->first();
      if(!driver){
        return response()->json([
          'success'=>false,
          'message'=>'Profil chauffeur introuvable.'
        ], 404);
      }
    }
}
