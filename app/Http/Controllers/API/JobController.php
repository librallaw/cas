<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JobController extends Controller
{
    //

    public function createJob(Request $request)
    {
        //Validate
        $validate = Validator::make($request->all(), [

            'job'     => "required",


        ]);

        if($validate->fails()){

            return response()->json(['error'=>$validate->errors()],'401');

        } else {

            var_dump($request->job);exit;

            $service = new Service();
            $service -> title  = $request -> title;
            $service -> church_id     = Auth::user()->unique_id;
            $service -> start_time     = $request -> start_time;
            $service -> service_date     = $request -> service_date;
            $service -> save();

            return response()->json([
                'status'    => true,
                'message'   => "Service created successfully",
                'service'    => $service
            ]);
        }

    }
}
