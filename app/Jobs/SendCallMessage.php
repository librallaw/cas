<?php

namespace App\Jobs;

use App\Credit;
use App\Members;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SendCallMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $user;
    private $unique_id;
    private $twilio_number;
    private $client;


    public function __construct(Members $user,$unique_id)
    {
        $this->user = $user;
        $this->unique_id = $unique_id;
        $this->twilio_number = "+12055764670";

        $account_sid = 'ACf8471e6404db9f94db1c8032942476d3';
        $auth_token = '4471dbf76b5aff510c3f84a37bce5579';
        // In production, these should be environment variables. E.g.:
        // $auth_token = $_ENV["TWILIO_ACCOUNT_SID"]

        $this->client = new \Twilio\Rest\Client($account_sid, $auth_token);
    }

    /**
     * Execute the job.
     * @throws TwilioException
     * @return void
     */
    public function handle()
    {


        //dd($this->user->phone_number);
        //echo "I gothere here here here and here";exit;


        $userid =  $this->unique_id;

        $call = Credit::where("user_id",$userid)->where("type","calcr")->first();

        if(!empty($call)){
            $audio = $call->message;
        }else{
            $audio = "http://demo.twilio.com/docs/classic.mp3";
        }

        $this->client->account->calls->create(

            $this->user->phone_number,
            $this->twilio_number,

            array(

                "twiml"=>"<Response><Play>$audio</Play></Response>",

            )
        );


        Log::info("Message was sent");



    }
}
