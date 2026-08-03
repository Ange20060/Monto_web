<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Driver;

class DriverController extends Controller
{
    public function profile(Request $request)
    {
       $driver = Driver::with('vehicle')
        ->where('user_id',$request->user()->id)
        ->first();
        if(!$driver){
          return response()->json([
            'success'=> false,
            'message'=> 'Profile chauffeur introuvable.'
          ], 404);
        }
        return response()->json([
          'success'=> true,
          'driver'=>$driver
        ]);
    }


    /**
     * Modifier le profil
     */
    public function update(Request $request){
      $driver = Driver::where('user_id', $request->user()->id)->first();

      if(!$driver){
        return response()->json([
          'sucess'=> false,
          'message'=> 'Profil chauffeur introuvable.'
        ], 404);
      }
      $request->validate([
        'phone'=>'nullable|string|max:20',
        'city'=>'nullable|string|max:100',
        'license_number'=>'nullable|string|max:100'
      ]);
      $driver->update($request->only([
        'phone',
        'city',
        'license_number'
      ]));

      return response()->json([
        'success'=>true,
        'message'=>'Profil mis à jour.',
        'data'=>$driver
      ]);
    }
    /**
     * Mettre le chauffeur en ligne
     */
    public function goOnline(Request $request)
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();

        $driver -> is_online =true;
        $driver ->save();

        return response()->json([
          'sucess'=> true,
          'message'=>'Vous êtes maintenant en ligne.'
        ]);
    }
    /**
     * Mettre le chauffeur hors ligne
     */
    public function goOffline(Request $request)
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();

        $driver -> is_online =false;
        $driver ->save();

        return response()->json([
          'sucess'=> true,
          'message'=>'Vous êtes maintenant hors ligne.'
        ]);
    }
    /**
     * Etat actuelle du chauffeur
     */
    public function status(Request $request)
    {
      $driver = Driver::where('user_id', $request->user()->id)->first();

      return response()->json([
        'online'=>$driver->is_online
      ]);
    }
}
