<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\SingleAttendanceResource;
use App\Members;
use App\Service;
use Illuminate\Http\Request;
use App\Attendance;
use App\Http\Controllers\Controller;

use Carbon\Carbon;

use Illuminate\Support\Facades\Auth;
use Validator;

class AttendanceController extends Controller
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



    public function store(Request $request)
    {


        $validate = Validator::make($request->all(), [

            'member_id'     => 'required',
        ]);

        if($validate->fails()){

            return response()->json([
                'status'=>false,
                'message' => 'Sorry your request could not be completed,please check the fields and try again',
                'errors' =>$validate->errors()->all() ,
            ], 401);

        }


        $username = $request->member_id;

        /* dd($_POST['username']);*/

                $user = Members::find($username);

                $date_form = Carbon::now()->format('Y-m-d');

                /**
                    Check if attendance has already been taken
                 */
                $check = Attendance::where('member_id', $username)
                    ->where('service_date', $date_form)
                    ->where('church_id', Auth::user()->unique_id)
                    ->get();



                if (empty($user)) {

                    return response()->json([
                        'status'=>false,
                        'message' => 'User not found',
                    ], 401);

                }


                if (count($check) > 0) {
                    // dd("I got here again 3");
                    return response()->json([
                        'status'=>false,
                        'message' => 'Attendance already taken',
                    ], 401);

                } else {


                    /**
                    Check if user is in the same chuch
                     */


                    if($user->church_id != Auth::user()->unique_id){
                        return response()->json([
                            'status'=>false,
                            'message' => 'You can only take attendance of users in your church',
                        ]);

                        exit;
                    }


                    $date =  Carbon::now()->format('Y-m-d');

                    /**
                    Check if service has already been created and create new service
                     */

                    $check_service = Service::where("church_id", Auth::user()->unique_id)
                        ->where("service_date",$date)
                        -> first();

                    if(empty($check_service)){

                        $new_service = new Service();
                        $new_service-> church_id = Auth::user()->unique_id;
                        $new_service-> service_date = $date;
                        $new_service-> save();

                    }


                    $seeparated_date = explode("-", $date);


                    $attendee = new Attendance();


                    $attendee -> church_id    = Auth::user()->unique_id;
                    $attendee -> member_id    = $request -> member_id;
                    $attendee -> arrival_time = time();
                    $attendee -> service_date = $date;

                    $attendee->year = $seeparated_date[0];
                    $attendee->month =$seeparated_date[1];
                    $attendee->day = $seeparated_date[2];

                    $attendee->group = $user->group_assigned;
                    $attendee->level = $user->level;

                    $attendee -> save();



                    return response()->json([
                        'status'=>true,
                        'message' => 'Attendance taken successfully',
                    ], 200);



                }


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function stores(Request $request)
    {
        //validate
        $validate = Validator::make($request->all(), [

            'member_id' => 'required',
        ]);

        if ($validate->fails()) {

            return response()->json([
                'status' => false,
                'message' => 'Sorry your request could not be completed,please check the fields and try again',
                'errors' => $validate->errors()->all(),
            ], 401);

        } else {

            $checker = Attendance::select('service_date')->where('church_id', Auth::user()->unique_id)->where('service_date', $request->service_date)->doesntExist();

            if ($checker) {

                $attend = new Attendance();
                $attend->church_id = Auth::user()->unique_id;
                $attend->member_id = $request->member_id;
                $attend->arrival_time = time();
                $attend->service_date = $request->service_date;
                $attend->service_type = $request->service_type;
                $attend->save();
                //dd($attend);


                return response()->json([
                    'status' => true,
                    'message' => "Attendance created successfully",
                    'attendance' => $attend

                ]);
            }

            return response()->json(['status' => true, 'message' => 'Attendance already taken for this service date']);

        }


    }





    public function singleAttendance(Request $request)
    {
        $validate = Validator::make($request->all(), [

            'service_date'     => 'required',
        ]);

        if($validate->fails()){

            return response()->json([
                'status'=>false,
                'message' => 'The date field is required',
                'errors' =>$validate->errors()->all() ,
            ], 401);

        }



        $attendance = Attendance::where("church_id",Auth::user()->unique_id)->where('service_date',
            $request->service_date)
            ->first();



        if(!empty($attendance))

            return response()->json([
                'status' => true,
                'data' => new SingleAttendanceResource($attendance),
            ]);

        else
            return response()->json([
                'status' => false,
                'message' => "No Attendance found for the requested date",
                'data' => [],
            ]);
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
}
