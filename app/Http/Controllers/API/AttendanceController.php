<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Attendance;
use App\Http\Controllers\Controller;
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //validate
        $validate = Validator::make($request->all(), [

            'member_id'     => 'required',
            'service_date'  => 'required',
            'service_type'  => 'required'
        ]);

        if($validate->fails()){

            return response()->json(['error'=>$validate->errors()],'401');

        } else {
            $attend = new Attendance();
            $attend -> church_id    = Auth::user()->unique_id;
            $attend -> member_id    = $request -> member_id;
            $attend -> arrival_time = time();
            $attend -> service_date = $request -> service_date;
            $attend -> service_type = $request -> service_type;
            $attend -> save();

            $checker = Attendance::select('id')->where('id',$request->member_id)->exists();


            if($checker){

                return back()->withInput();
                //return response()->json(['status'=> true, 'message'=>'Record Exists']);
            }

            return response()->json([

                'status'    => true,
                'message'   => "Attendance created successfully",
                'attendance'    => $attend

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
}
