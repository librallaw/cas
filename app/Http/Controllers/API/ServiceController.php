<?php

namespace App\Http\Controllers\API;

use App\Attendance;
use Illuminate\Http\Request;
use App\Service;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


use Validator;


class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Validate
        $validate = Validator::make($request->all(), [

            'church_id'     => Auth::user()->unique_id,
            'service_type'  => 'required'
        ]);

        if($validate->fails()){

            return response()->json(['error'=>$validate->errors()],'401');

        } else {
            $service = new Service();
            $service -> service_type  = $request -> service_type;
            $service -> church_id     = Auth::user()->unique_id;
            $service -> save();

            return response()->json([
                'status'    => true,
                'message'   => "Service created successfully",
                'service'    => $service
            ]);
        }

    }


    public function lastService()
    {



        $service = Service::where("church_id",Auth::user()->unique_id)->latest()->first();





        if(count($service) > 0) {


            return response()->json([
                'status' => true,
                'message' => "Successfully",
                'data' => $service->attendance,
                'attendance' => count($service->attendance)
            ]);
        }


        else {

            return response()->json([
                'status' => true,
                'message' => "No service found for this service",
                'data' => [],
                'attendance' => 0
            ]);

        }



    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function service_list() {




        $services_arry = array();

        if(isset($_GET['num'])){

            $services   =  Service::where("church_id",Auth::user()->unique_id)->take($_GET['num'])->get();
        } else {
            $services   =  Service::where("church_id",Auth::user()->unique_id)->get();
        }




        foreach ($services as $service){
            $services_arry[] = array(
                "service_date" => $service->service_date,
                "attendance" => count($service->attendance),
            );
        }


        return response()->json([
            'status'    => true,
            'data'      => $services_arry
        ]);
    }


    public function compareServices(Request $request)
    {

        //Validate
        $validate = Validator::make($request->all(), [
            'service1' =>'required',
            'service2' =>'required',
        ]);

        if($validate->fails()){

            return response()->json([
                'status' => false,
                'message' => "All fields are required",
                'errors' => $validate->errors()
            ],401);

        };


        $services1 = $request->post("service1");
        $services2 = $request->post("service2");



        $service1_attend = Attendance::where("service_date",$services1)->where("church_id",Auth::user()->unique_id)
            ->get();
        $service2_attend = Attendance::where("service_date",$services2)->where("church_id",Auth::user()->unique_id)
            ->get();

        if($services1 == $services2){

            return response()->json([
                'status' => false,
                'message' => "The same service cannot be compared, please select two different services",
            ],301);

           exit;
        }

        if(count($service1_attend) < 1){

            return response()->json([
                'status' => false,
                'message' => $services1." has no attendee",
            ],301);

            exit;

        }

        if(count($service2_attend) < 1){

            return response()->json([
                'status' => false,
                'message' => $services2." has no attendee",
            ],301);

            exit;
        }


        // dd($service1_attend);

        $result = array();
        $x = 0;
        $absentees  = 0;
        $present  = 0;


        foreach($service1_attend as $attendee){

            if(empty($attendee->user->firstname)){
                continue;
            }

            $service2  = Attendance::where('member_id',$attendee->user_id)->where("service_date",$services2)->get();


            if(count($service2) > 0){
                $result[$x]['user_id'] = $attendee->user_id;
                $result[$x]['username'] = $attendee->user->full_name();
                $result[$x]['group'] = $attendee->user->group;
                $result[$x]['cell'] = $attendee->user->cell;
                $result[$x]['phone'] = $attendee->user->phone;
                $result[$x]['status'] = "present";
                $result[$x]['date'] = $services2;
                $result[$x]['type'] = "success";
                $present++;
            }else{
                $result[$x]['user_id'] = $attendee->user_id;
                $result[$x]['username'] = $attendee->user->full_name();
                $result[$x]['group'] = $attendee->user->group;
                $result[$x]['cell'] = $attendee->user->cell;
                $result[$x]['phone'] = $attendee->user->phone;
                $result[$x]['status'] = "absent";
                $result[$x]['date'] = $services2;
                $result[$x]['type'] = "danger";
                $absentees ++;
            }
            $x++;
        }



//        header("Content-Type: application/json");
//       die(json_encode($result));exit;
        // dd($result);

        $data['present'] = $present;
        $data['absentees'] = $absentees;
        $data['total'] = count($service1_attend);
        $data['results'] = $result;
        $data['services1'] = $services1;
        $data['services2'] = $services2;

        return view("admin.attendance.compare",$data);

//        header("Content-Type: application/json");
//        die(json_encode($result));exit;

    }


}
