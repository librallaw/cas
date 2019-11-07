<?php

namespace App\Http\Controllers\API;

use App\Members;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FirstTimersController extends Controller
{
    //
    public function showFirstTimers(){

        $first_timers = Members::where('church_id',Auth::user()->unique_id)->where('first_timer',1)->get();

        if(count($first_timers)){

            return response()->json([
                'status'    => true,
                'data'      => $first_timers
            ]);

        } else{
            return response()->json([
                'status'    => false,
                'message'   => 'No First timer found.',
                'data' => []
            ]);
        }



    }
}
