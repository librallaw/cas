<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Libraries\Messenger;
use App\Attendance;
use App\Credit;
use App\Job;
use App\Members;


class CallFollowUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $jobs  = Job::where("follow_type",'cfu')->where("status",0) -> where("run_time","<=",$current_time)->get();

        echo "ss";

        foreach ($jobs as $job){

            $credit = Credit::where("user_id", $job->unique_id)
                ->where("type","calcr")
                ->first();

            //select members that were present
            $attendees =  Attendance::where("service_date",$job->service_date)
                ->where("church_id",$job->unique_id)
                ->pluck("member_id")->toArray();

            //select members that were absent
            $absentees = Members::whereNotIn("id",$attendees)
                ->where("church_id",$job->unique_id)
                ->where("email","!=","")
                ->get();


            //  dd($absentees);



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
                //dd(count($absentees));

                for ($i = 0; ($i < count($absentees) && $rb > 0); $i++,$rb--,$sent++) {

                    //dd($absentee -> full_name);
                    //send email

                    $messenger ->sendEmail($absentees[$i]->full_name." <".$absentees[$i]->email.">",'Keep Track
                     <noreply@internetmultimediaonline.org>','Pastor is looking for you ','Hi '.
                        $absentees[$i]->full_name . ', ' . $credit->message);

                    //dd($messenger);

                    echo $i;

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

                    echo "Sent ".($sent -1)." out of ".count($absentees);

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
