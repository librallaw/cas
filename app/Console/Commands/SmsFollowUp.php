<?php

namespace App\Console\Commands;

use App\Libraries\Messenger;
use Illuminate\Console\Command;
use App\Attendance;
use App\Credit;
use App\Job;
use App\Members;

class SmsFollowUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:follow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send sms notification to members of a church';

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
    public function handle(Messenger $messenger)
    {
        //


        $current_time = time();
        $smsjobs  = Job::where("follow_type",'sfu')->where("status",0) -> where("run_time","<=",$current_time)->get();


        //   dd($smsjobs);
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



            $credit_balance = $credit -> balance;

            //dd($credit_balance);
            //check if the user still hs credit

            $success = 0;
            $failed = 0;

            if($credit_balance > 0) {

                //  echo "I got here";exit;

                $remaining = $credit_balance;

                // dd(count($absentees));

                $sent = 0;
                $rb = $remaining;

                for ($i = 0; ($i < count($absentees) && $rb > 0); $i++,$rb--,$sent++) {

                    //send sms to the member via sms micro service
                    $messenger ->sendText($absentees[$i]->phone_number,'Keep Track','Hi ' . $absentees[$i]->full_name . ', ' . $credit->message);

                }


                //update new balance to the user's account
                $credit ->balance = $rb;
                $credit -> save();


                echo "remaining-balance= ".$rb."<br />";
                echo "sent = ".$sent."<br />";
                echo "failed = ".(count($absentees) - (int) $sent)."<br />";


                $success = $sent;
                $failed = (count($absentees) - (int) $sent);

                //check if the user's credit finished on the road
                if(count($absentees) != $sent){
                    //amount of sms remaining to be sent
                    $amount_remainig = count($absentees) - $sent;

                    echo "Sent ".($sent )." out of ".count($absentees);

                    // sent notification email to the user informing of the development
                }


            }else{

                continue;
            }


            //update job with status

            $job = Job::where("id",$job->id)->first();

            $job->status = 1;
            $job->success = $success;
            $job->failed = $failed;

            $job ->save();




        }


    }
}
