<?php

namespace App\Http\Controllers\API;

use App\Attendance;
use App\Members;
use Illuminate\Http\Request;
use App\Service;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


use Validator;


class DataCaptringController extends Controller
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
    public function auth(Request $request)
    {
        //Validate
        $validate = Validator::make($request->all(), [

            'email'     => "required",
        ]);

        if($validate->fails()){

            return response()->json(['error'=>$validate->errors()],'401');

        } else {
            $member = Members::where("email",$request->email)->first();


            if(!empty($member)){
                return response()->json([
                    'status'    => true,
                    'message'   => "Service created successfully",
                    'data'    => $member
                ]);
            }else{
                return response()->json([
                    'status'    => false,
                    'message'   => "Member not found ",
                ],404);
            }


        }

    }




}
