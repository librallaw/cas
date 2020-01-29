<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestingController extends Controller
{
    //
    public function index()
    {

        //echo "testing microppe";exit;
        // Your Account SID and Auth Token from twilio.com/console
        $account_sid = 'ACf8471e6404db9f94db1c8032942476d3';
        $auth_token = '4471dbf76b5aff510c3f84a37bce5579';
        // In production, these should be environment variables. E.g.:
        // $auth_token = $_ENV["TWILIO_ACCOUNT_SID"]

        // A Twilio number you own with Voice capabilities
        $twilio_number = "+12055764670";

        // Where to make a voice call (your cell phone?)
        $to_number = "+2348141355303";

        $client = new \Twilio\Rest\Client($account_sid, $auth_token);
        $client->account->calls->create(
            $to_number,
            $twilio_number,

            array(
                "twiml"=>'<Response><Say>Hello Sister Marvelous, You need to be on your way to church now, service has started!</Say></Response>',
               // "twiml"=>'<Response><Play>http://demo.twilio.com/docs/classic.mp3</Play></Response>',

            )
        );


    }
}
