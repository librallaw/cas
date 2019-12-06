<?php

namespace App\Http\Controllers;

use App\Attendance;
use App\Credit;
use App\Job;
use App\Libraries\Messenger;
use App\Members;
use App\User;
use Illuminate\Http\Request;
use Mailgun\Mailgun;
use Nexmo\Client;

class TestController extends Controller
{
    //

    public function index(Messenger $messenger)
    {
        //

        $current_time = time();
        $jobs  = Job::where("follow_type",'efu')->where("status",0) -> where("run_time","<=",$current_time)->get();

echo "ss";

        foreach ($jobs as $job){

            $credit = Credit::where("user_id", $job->unique_id)
                ->where("type","emcr")
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

    public function index5()
    {


# Instantiate the client.
        $mgClient =  Mailgun::create('key-ad76d0aa5b0e6fa5ad037cb4efbb7c91');
        $domain = "internetmultimediaonline.org";
# Make the call to the client.
        $result = $mgClient->messages()->send($domain, array(
            'from'	=> 'Excited User <noreply@internetmultimediaonline.org>',
            'to'	=> 'Lawrence Libral <librallaw@gmail.com>',
            'subject' => 'Hello',
            'text'	=> 'Testing some Mailgun awesomness!'
        ));
    }

    public function index4()
    {
        //

        $current_time = time();
        $jobs  = Job::where("follow_type",'efu')->where("status",0) -> where("run_time","<=",$current_time)->get();


     //   dd($smsjobs);
        foreach ($jobs as $job){

            $credit = Credit::where("user_id", $job->unique_id)
                ->where("type","emcr")
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



            $credit_balance = $credit -> balance;
            //dd($credit_balance);

            $basic  = new \Nexmo\Client\Credentials\Basic("c9f6fb8a", "9QGW1oIINCiI1Jgz");
            $client = new \Nexmo\Client($basic);

            //check if the user still hs credit
            $success = 0;
            $failed = 0;

            if($credit_balance > 0) {

              //  echo "I got here";exit;

                $remaining = $credit_balance;

               // dd(count($absentees));

                $sent = 0;
                $rb = $remaining;

                for ($i = 1; ($i <= count($absentees) && $rb > 0); $i++,$rb--,$sent++) {

                    // dd($absentee -> full_name);
                    //send sms
//                    try {
//                        $message = $client->message()->send([
//                            'to' => $absentees[$i]->phone_number,
//                            'from' => 'CLVZ',
//                            'text' => 'Hi ' . $absentees[$i]->full_name . ', ' . $credit->message
//                        ]);
//                        $response = $message->getResponseData();
//
//                        if ($response['messages'][0]['status'] == 0) {
//                            echo "The message was sent successfully\n";
//                        } else {
//                            echo "The message failed with status: " . $response['messages'][0]['status'] . "\n";
//                        }
//                    }catch (Exception $e) {
//                        continue;
//
//
//                    }

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


            $job = Job::where("id",$job->id)->first();

            $job->status = 1;
            $job->success = $success;
            $job->failed = $failed;

            $job ->save();




        }


    }

    public function index3()
    {
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

    public function index2()
    {
        $current_time = time();
        $emailjobs  = Job::where("follow_type",'efu')->where("status",0) ->where("run_time",">=",$current_time)->get();


        foreach ($emailjobs as $job){

                //select members that were present
                $attendees =  Attendance::where("service_date",$job->service_date)
                    ->where("church_id",$job->unique_id)
                    ->pluck("member_id")->toArray();

                //select members that were absent
                $absentees = Members::whereNotIn("id",$attendees)->
                where("church_id",$job->unique_id)
                    ->where("email","!=","")->pluck("email")->toArray();



                foreach ($absentees as $absentee){
                    //send meial
                }


            $job = Job::where("id",$job->id);

            $job->status = 1;
            $job ->save();


                //send email to them

                //$jobs = User::where("unique_id",$job->unique_id)->first();

        }
   }
}
