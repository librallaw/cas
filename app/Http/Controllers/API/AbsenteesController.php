<?php

namespace App\Http\Controllers\API;

use App\Absentee;
use App\Attendance;
use App\Members;
use App\Service;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;

class AbsenteesController extends Controller
{
    //

    //
    public function generateAbsentees($dated,$group="")
    {



        $date = Service::where("service_date",$dated)->where("church_id",Auth::user()->unique_id)->first();



        if(!empty($date)){

            //select members that were present
           $attendees =  Attendance::where("service_date",$dated)->where("church_id",Auth::user()->unique_id)->pluck("member_id")->toArray();

           //select members that were absent
            $absentees = Members::whereNotIn("id",$attendees)->where("church_id",Auth::user()->unique_id)->pluck("id")->toArray();

           // dd($absentees);


            $date -> absentees_count = count($absentees);
            $date -> absentees = implode(",",$absentees);
            $date -> save();

            return response()->json([
                'status' => true,
                'message' => "Absentees successfully generated",
                "data" =>$date -> absentees_count
            ]);


        }else{
            return response()->json([
                'status' => false,
                'message' => "You have no attendance for the requested service"
            ]);
        }


        if(!empty($group)) {

            $grouped = (string)$group;

            $data['groups'] = Absentee::where("date", $date)->distinct()->get(['group']);
            $data['records'] = Absentee::where("date",$date)->where('church',Auth::user()->church)->where("group",$grouped)->get();
            $data['date'] =$date;

        }else{

            $data['groups'] = Absentee::where("date", $date)->distinct()->get(['group']);
            $data['records'] = Absentee::where("date",$date)->where('church',Auth::user()->church)->get();

            $data['date'] =$date;
        }



        return view("admin.attendance.absentees",$data);
    }


    public function generateMultipleAbsentees (Request $request)
    {

        $validate = Validator::make($request->all(), [
            'services' =>'required',
        ]);

        if($validate->fails()){

            return response()->json([
                'status' => false,
                'message' => "You need to select at least one service",
                'errors' => $validate->errors()
            ],401);

        };



        $services_array = array();
        $services_in = $request -> post("services");
        $services = explode(",",$services_in);

        $services_count = 0;

        //add all arrays into a singe array
        foreach ($services as $service){
            $serv = Service::where("service_date",$service)->first();

            if($serv->absentees != 0 && $serv->absentees !=null) {
                $services_array[] = explode(",", $serv->absentees);
                $services_count++;
            }
        }




        //merge all array into a single array

        $final_serv_array = array_count_values(array_merge(...$services_array));




        //select members that appeared absent in all services
        $members = array();
        foreach ($final_serv_array as $key => $value){
            if($value == $services_count){
                $members[] = $key;
            }
        }


        $absentees = Members::whereIn("id",$members)->get();

        return response()->json([
            "status" => true,
            "count" => count($absentees),
            "data" => $absentees,
            "services" => $request -> post("services")
        ]);





    }
}
