<?php

namespace App\Http\Controllers\API;

use App\Credit;
use App\Imports\First_timersImport;
use App\Members;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;



class FirstTimersController extends Controller
{
    //
    public function showFirstTimers(){

        $first_timers = Members::where('church_id',Auth::user()->unique_id)->where('first_timer',1)->get();

        if(count($first_timers)){

            return response()->json([
                'status'    => true,
                'data'      => $first_timers
            ]);

        } else  {
            return response()->json([
                'status'    => false,
                'message'   => 'No First timer found.',
                'data' => []
            ]);
        }



    }



    public function createFirstTimers(Request $request)
    {
        //validate
        $validator = Validator::make($request->all(), [
            'title'             => 'required',
            'full_name'         => 'required',
            'gender'            => 'required',
            'birth_date'        => 'required',
            'phone_number'      => 'required',
            'email'             => 'required|email',
            'marital_status'    => 'required',
            'group_assigned'    => 'required',
            'home_address'      => 'required'
        ]);
        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message' => 'Sorry your reqistration could not be completed',
                'errors' =>$validator->errors()->all() ,
            ], 401);
        } else {
            $mem = new Members();
            $mem->title             = $request->title;
            $mem->full_name         = $request->full_name;
            $mem->gender            = $request->gender;
            $mem->birth_date        = $request->birth_date;
            $mem->phone_number      = $request->phone_number;
            $mem->email             = $request->email;
            $mem->marital_status    = $request->marital_status;
            $mem->group_assigned    = $request->group_assigned;
            $mem->home_address      = $request->home_address;
            $mem->church_id         = Auth::user()->unique_id;
            $mem->first_timer         = 1;
            $mem->save();
            //$mem = Members::create($request->all());

            $success['full_name'] =  $mem->full_name;

            return response()->json([
                'status'    => true,
                'message'   => "First timers successfully created.",
                'data'    => $mem
            ]);

        }

    }


    public function confirm_payment(Request $request)
    {


        //var_dump($_POST);exit;

         echo "I got here";exit;


        $curl = curl_init();
        $reference = isset($_POST['reference']) ? $_POST['reference'] : '';
        if (!$reference) {
            die('No reference supplied');
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "authorization: Bearer " . env("PAYSTACK_SECRET"),
                "cache-control: no-cache"

            ],
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        if ($err) {
            // there was an error contacting the Paystack API
            die('Curl returned error: ' . $err);
        }

        $tranx = json_decode($response);

        //dd($tranx);

        if (!$tranx->status) {
            // there was an error from the API
            die('API returned error: ' . $tranx->message);
        }

        if ('success' == $tranx->data->status) {

            $unique_id = $tranx->data->reference;

            //fetch from the transaction table
            $payment = Payment::where('reference',$unique_id)->first();

            //Check if value has already been given to the user
            if(count($payment) > 0){

                return response()->json([
                    'status' => false,
                    'message' => "Value already given for this transaction"
                ],400);

            }

            $credit = $request->amount;
            $type = $request->type;

            //check for the amount of credit for package and convert from kobo to Naira
            $money_payed = $tranx->data->amount / 100;

            switch ($type){

                case "emcr":
                    $per_credit = 10;
                    break;

                case "smscr":
                    $per_credit = 2;
                    break;

                case "calcr":
                    $per_credit = 2;
                    break;

            }

            //get credit that user's money can buy
            $legal_credit = $money_payed / $per_credit;

            //check for fradulent transaction

            /**
            If the credit reqested for does not match the exact money that was sent to the gateway
             * then the transaction was fraudulent
             *
             */


            if($legal_credit != $credit){

                return response()->json([
                    'status' => false,
                    'message' => "There is an issue with your transaction code: 9821",
                    'legal_credit' => $legal_credit,
                    'per_credit' => $per_credit,
                ],400);
            }else{

                $payment = new Payment();
                $payment->trans_id = $tranx->data->id;
                $payment->unique_id = Auth::user()->unique_id;
                $payment->module_id = $type;
                $payment->amount = $tranx->data->amount;
                $payment->currency = $tranx->data->currency;
                $payment->transaction_date = $tranx->data->transaction_date;
                $payment->status = $tranx->data->status;
                $payment->reference = $tranx->data->reference;
                $payment->gateway_response = $tranx->data->gateway_response;
                $payment->ip_address = $tranx->data->ip_address;
                $payment->complete_time = time();

                $payment->authorization_code = $tranx->data->authorization->authorization_code;
                $payment->bin = $tranx->data->authorization->bin;
                $payment->last4 = $tranx->data->authorization->last4;
                $payment->exp_month = $tranx->data->authorization->exp_month;
                $payment->exp_year = $tranx->data->authorization->exp_year;
                $payment->channel = $tranx->data->authorization->channel;
                $payment->card_type = $tranx->data->authorization->card_type;
                $payment->bank = $tranx->data->authorization->bank;
                $payment->country_code = $tranx->data->authorization->country_code;
                $payment->brand = $tranx->data->authorization->brand;
                $payment->reusable = $tranx->data->authorization->reusable;
                $payment->signature = $tranx->data->authorization->signature;
                $payment->date = date('m-y',time());

                $payment->save();


                $credit =  Credit::where("user_id",Auth::user()->unique_id )->where("type",$type)->first();

                if(!empty($credit)){
                    $credit->balance = $credit->balance + $legal_credit;
                    $credit->save();

                }else{
                    $credit = new Credit();
                    $credit->user_id = Auth::user()->unique_id;
                    $credit->balance = $legal_credit;
                    $credit->type = $type;
                    $credit->message = "This is test message and it is going to be deleted, so I want you to ignore it because it is going to be deleted";
                    $credit -> save();

                }

                //return error message back to the user if the transaction does not exist
                return response()->json([
                    'status' => true,
                    'data' => $credit,
                    'per_credit' => $per_credit,
                ]);

            }


        } else {

            return response()->json([
                'status' => false,
                'message' => "Sorry there an error occurred while processing your transaction, please try again later"
            ]);

        }
    }

    public function updateDefaultMessage(Request $request)
    {
        //validate
        $validator = Validator::make($request->all(), [
            'message'             => 'required',
            'type'             => 'required',

        ]);


        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message' => 'Sorry your reqistration could not be completed',
                'errors' =>$validator->errors()->all() ,
            ], 401);
        } else {

            $mem = Credit::where("user_id",Auth::user()->unique_id)->where("type",$request->type)->first();

            if(!empty($mem)){
                $mem->message             = $request->message;
                $mem->save();
            }else{
                $credit = new Credit();
                $credit->user_id = Auth::user()->unique_id;
                $credit->balance = 0;
                $credit->type = $request->type;
                $credit -> save();

            }

            //$mem = Members::create($request->all());

            return response()->json([
                'status'    => true,
                'message'   => "Message Successfully updated.",
            ]);

        }

    }

    public function batchUpload(Request $request) {

        if($request->hasFile('file')) {

            $path = $request->file('file')->getRealPath();

            //Excel::import(new CsvImport, request()->file('file'));
            $import = new First_timersImport();
            $import->import(request()->file('file'));

            foreach ($import->failures() as $failure) {
                $failure->row(); // row that went wrong
                $failure->attribute(); // either heading key (if using heading row concern) or column index
                $failure->errors(); // Actual error messages from Laravel validator
                $failure->values(); // The values of the row that has failed.
            }


            //dd($failure->row());


            return response()->json([
                'status'    => true,
                'message'   => "First timers Successfully uploaded.",
            ]);

        } else {
            return response()->json([
                'status' => false,
                'message' => "No record to upload.",
            ]);
        }

    }


}
