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


        $basic  = new \Nexmo\Client\Credentials\Basic("c9f6fb8a", "9QGW1oIINCiI1Jgz");
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
        $mgClient =  Mailgun::create('key-ad76d0aa5b0e6fa5ad037cb4efbb7c91');
        $domain = "internetmultimediaonline.org";
# Make the call to the client.
        $result = $mgClient->messages()->send($domain, array(
            'from'	=> $from,//'Excited User <noreply@internetmultimediaonline.org>',
            'to'	=> $to,//Lawrence Libral <librallaw@gmail.com>',
            'subject' => $subject,
            'text'	=> $text
        ));
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
