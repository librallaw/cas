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



    public function createFirstTimers(Request $request)
    {
        //validate
        $validator = Validator::make($request->all(), [
            'title'             => 'required',
            'full_name'         => 'required',
            'gender'            => 'required',
            'birth_date'        => 'required',
            'phone_number'      => 'required',
            'email'             => 'required|email',
            'marital_status'    => 'required',
            'group_assigned'    => 'required',
            'home_address'      => 'required'
        ]);
        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message' => 'Sorry your reqistration could not be completed',
                'errors' =>$validator->errors()->all() ,
            ], 401);
        } else {
            $mem = new Members();
            $mem->title             = $request->title;
            $mem->full_name         = $request->full_name;
            $mem->gender            = $request->gender;
            $mem->birth_date        = $request->birth_date;
            $mem->phone_number      = $request->phone_number;
            $mem->email             = $request->email;
            $mem->marital_status    = $request->marital_status;
            $mem->group_assigned    = $request->group_assigned;
            $mem->home_address      = $request->home_address;
            $mem->church_id         = Auth::user()->unique_id;
            $mem->first_timer         = 1;
            $mem->save();
            //$mem = Members::create($request->all());

            $success['full_name'] =  $mem->full_name;

            return response()->json([
                'status'    => true,
                'message'   => "First timers successfully created.",
                'data'    => $mem
            ]);

        }

    }
}
