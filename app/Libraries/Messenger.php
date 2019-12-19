<?php

namespace App\Libraries;

use App\Loan;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use GuzzleHttp\Exception\ClientException;
use Mailgun\Mailgun;
use Validator;


class Messenger
{
    //

    public function sendText($to,$from,$text)
    {


        $basic  = new \Nexmo\Client\Credentials\Basic(env("NEXMO_KEY"), env("NEXMO_SECRET"));
        $client = new \Nexmo\Client($basic);


        try {
            $message = $client->message()->send([
                'to' => $to,
                'from' => $from,
                'text' => $text
            ]);
            $response = $message->getResponseData();

            if ($response['messages'][0]['status'] == 0) {
                $resp =  "The message was sent successfully\n";
            } else {
                $resp = "The message failed with status: " . $response['messages'][0]['status'] . "\n";
            }

            return $resp;

        }catch (Exception $e) {

            $resp = "An error occurred from the api";
            return $resp;


        }


    }




    public function sendEmail($to, $from,$subject,$text)
    {

//       $benz =  array(
//            'from'	=> $from,//'Excited User <noreply@internetmultimediaonline.org>',
//            'to'	=> $to,//Lawrence Libral <librallaw@gmail.com>',
//            'subject' => $subject,
//            'text'	=> $text
//        );
//
//
//       dd($benz);
        $mgClient =  Mailgun::create(env('MAILGUN_API_KEY'));
        $domain = "internetmultimediaonline.org";
# Make the call to the client.
        $result = $mgClient->messages()->send($domain, array(
            'from'	=> $from,//'Excited User <noreply@internetmultimediaonline.org>',
            'to'	=> $to,//Lawrence Libral <librallaw@gmail.com>',
            'subject' => $subject,
            'text'	=> $text
        ));
    }


    public function make_call($phone_number,$unique_id)
    {
        // Your Account SID and Auth Token from twilio.com/console
        $account_sid = 'ACf8471e6404db9f94db1c8032942476d3';
        $auth_token = '1626182520540c3a99d097fa6835fcfd';
        // In production, these should be environment variables. E.g.:
        // $auth_token = $_ENV["TWILIO_ACCOUNT_SID"]

        // A Twilio number you own with Voice capabilities
        $twilio_number = "+12055764670";

        // Where to make a voice call (your cell phone?)
        $to_number = $phone_number;

        $client = new \Twilio\Rest\Client($account_sid, $auth_token);
        $client->account->calls->create(
            $to_number,
            $twilio_number,
            array(
                "url" => "http://apis.keeptrack.online/api/user/voice?user_id=".$unique_id
            )
        );
    }


    public function twiloCall()
    {



// Your Account SID and Auth Token from twilio.com/console
$account_sid = 'ACf8471e6404db9f94db1c8032942476d3';
$auth_token = '1626182520540c3a99d097fa6835fcfd';
// In production, these should be environment variables. E.g.:
// $auth_token = $_ENV["TWILIO_ACCOUNT_SID"]

// A Twilio number you own with Voice capabilities
$twilio_number = "+12055764670";

// Where to make a voice call (your cell phone?)
$to_number = "+2347039592166";

$client = new \Twilio\Rest\Client($account_sid, $auth_token);
$client->account->calls->create(
    $to_number,
    $twilio_number,
    array(
        "url" => "http://demo.twilio.com/docs/voice.xml"
    )
);

    }





    public function random_num($size) {
        $alpha_key = '';
        $keys = range('A', 'Z');

        for ($i = 0; $i < 2; $i++) {
            $alpha_key .= $keys[array_rand($keys)];
        }

        $length = $size - 2;

        $key = '';
        $keys = range(0, 9);

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }

        return $alpha_key . $key;
    }



    public function randomId($num,$column,$table){

        $id = $this->random_num($num);

        $validator = \Validator::make(["$column"=>$id],['id'=>"unique:$table,reference"]);

        if($validator->fails()){
            return $this->randomId($num);
        }

        return $id;
    }





}
