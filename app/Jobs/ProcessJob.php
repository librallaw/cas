<?php

namespace App\Jobs;

use App\Attendance;
use App\Credit;
use App\Job;
use App\Jobs\SendCallMessage;
use App\Libraries\Messenger;
use App\Members;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $jobion;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($jobion)
    {
        //
        $this->jobion = $jobion;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //

            $credit = Credit::where("user_id", $this->jobion->unique_id)
                ->where("type","calcr")
                ->first();

            //select members that were present
            $attendees =  Attendance::where("service_date",$this->jobion->service_date)
                ->where("church_id",$this->jobion->unique_id)
                ->pluck("member_id")->toArray();

            //select members that were absent
            $absentees = Members::whereNotIn("id",$attendees)
                ->where("church_id",$this->jobion->unique_id)
                ->where("phone_number","!=","")
                ->get();

            //dd($absentees);

            $credit_balance = $credit -> balance;

            //dd($credit_balance);
            //check if the user still hs credit

            $success = 0;
            $failed = 0;

            if($credit_balance > 0) {

                // echo "I got here";exit;

                $remaining = $credit_balance;

                // dd(count($absentees));

                $sent = 0;
                $rb = $remaining;


                //  dd($absentees);

                for ($i = 0; ($i < count($absentees) && $rb > 0); $i++,$rb--,$sent++) {

                    //send sms to the member via sms micro service

                    SendCallMessage::dispatch(Members::find($absentees[$i]->id),$this->jobion->unique_id);

                    //$messenger->make_call("+".$absentees[$i]->phone_number,$this->jobion->unique_id);


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

                //continue; notify user of low Balance
            }


            //update job with status

            $job = Job::where("id",$this->jobion->id)->first();

            $this->jobion->status = 1;
            $this->jobion->success = $success;
            $this->jobion->failed = $failed;

            $job ->save();




        
    }
}
