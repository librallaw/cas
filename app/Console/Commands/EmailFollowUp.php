<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Attendance;
use App\Credit;
use App\Job;
use App\Members;


class EmailFollowUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:follow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email notification to members of a church';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //

        $current_time = time();
        $smsjobs  = Job::where("follow_type",'sfu')->where("status",0) ->where("run_time",">=",$current_time)->get();




        foreach ($smsjobs as $job){

            $credit = Credit::where("user_id", $job->unique_id)
                ->where("type","smscr")
                ->first();

            //select members that were present
            $attendees =  Attendance::where("service_date",$job->service_date)
                ->where("church_id",$job->unique_id)
                ->pluck("member_id")->toArray();

            //select members that were absent
            $absentees = Members::whereNotIn("id",$attendees)
                ->where("church_id",$job->unique_id)
                ->where("phone_number","!=","")
                ->get();




            $basic  = new \Nexmo\Client\Credentials\Basic("c9f6fb8a", "9QGW1oIINCiI1Jgz");
            $client = new \Nexmo\Client($basic);

            foreach ($absentees as $absentee) {

                // dd($absentee -> full_name);
                //send sms
                try {
                    $message = $client->message()->send([
                        'to' => $absentee->phone_number,
                        'from' => 'CLVZ',
                        'text' => 'Hi '.$absentee -> full_name.', '.$credit->message
                    ]);
                    $response = $message->getResponseData();

                    if ($response['messages'][0]['status'] == 0) {
                        echo "The message was sent successfully\n";
                    } else {
                        echo "The message failed with status: " . $response['messages'][0]['status'] . "\n";
                    }
                } catch (Exception $e) {


                }
            }


            $job = Job::where("id",$job->id)->first();

            $job->status = 1;
            $job ->save();




        }


    }
}
