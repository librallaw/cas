<?php

namespace App\Http\Controllers;

use App\Credit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class VoiceController extends Controller
{
    //

    public function returnVoice()
    {
        $userid = $_GET['user_id'];

        $call = Credit::where("user_id",$userid)->where("type","calcr")->first();

        if(!empty($call)){
            $audio = $call->message;
        }else{
            $audio = "http://demo.twilio.com/docs/classic.mp3";
        }

        return response()->json([
            "status" => true,
            "audio" => $audio
        ]);

    }


    public function uploadAudio(Request $request)
    {
        //


        $validate = Validator::make($request->all(), [
            'file' =>'required',
        ]);

        if($validate->fails()){

            return response()->json([
                'status' => false,
                'message' => "You need to select file",
                'errors' => $validate->errors()
            ],401);

        };



        $credit = Credit::where("user_id",Auth::user()->unique_id)->where("type","calcr")->first();



        $imageName = time(). '.mp3';


        $s3 = new \S3('AKIAYIMTQ7ZNUX4GSC57','kNc/d572ntscpDWcwamoTdA8nfqKiZymzBZ6RbgT' );

        if ($s3->putObjectFile($request->file('file'), "vcp-blw", "timeline/cei/products/images/" .
            $imageName,
            $s3::ACL_PUBLIC_READ)) {
            $credit->message = "http://vcp-blw.s3.amazonaws.com/timeline/cei/products/images/".$imageName;
            $credit ->save();

            return response()->json([
                'status' => true,
                'message' => "Voice successfully added",
            ],200);

        }else{
            return response()->json([
                'status' => true,
                'message' => "An error occured, please try again later",
            ],401);
        }






    }
}
