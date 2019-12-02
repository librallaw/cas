<?php

namespace App\Http\Controllers\API;

use App\Job;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;

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
            $jobs = $request->job;

            //create new
            if(count($jobs) > 0){
                foreach($jobs as $job){
                    $newJob = new Job();
                    $newJob -> unique_id = Auth::user()->unique_id;
                    $newJob -> run_time = $job['run_time'];
                    $newJob -> service_date = $job['service_date'];
                    $newJob -> follow_type = $job['follow_type'];
                    $newJob->save();
                }
            }

         //dispatch notification to user that the job has been queued

        }

    }
}
