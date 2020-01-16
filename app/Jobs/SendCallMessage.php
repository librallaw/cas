<?php

namespace App\Jobs;

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

        $this->client->account->calls->create(

            $this->user->phone_number,
            $this->twilio_number,

            array(
                "url" => "http://apis.keeptrack.online/api/user/voice?user_id=".$this->unique_id
            )
        );


        Log::info("Message was sent");



    }
}
