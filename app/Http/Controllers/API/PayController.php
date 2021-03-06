<?php

namespace App\Http\Controllers\API;

use App\Credit;
use App\Payment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use Validator;

class PayController extends Controller
{
    //
    public function confirm2_payment(Request $request)
    {

        return response()->json([
            'status' => false,
            'data' => "kkkk"
        ]);

        var_dump($_POST);exit;

        // echo "I got here";exit;


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

    public function returnUserCredit()
    {
        $credit = Credit::where("user_id",Auth::user()->unique_id)->where("type","smscr")->first();

        if(!empty($credit)){
            $sms_credit = $credit->balance;
            $sms_message = $credit->message;
        }else{
            $sms_credit = 0;
            $sms_message  = "";
        }

        $ecredit = Credit::where("user_id",Auth::user()->unique_id)->where("type","emcr")->first();
        if(!empty($ecredit)){
            $email_credit = $ecredit->balance;
            $email_message = $ecredit->message;
        }else{
            $email_credit = 0;
            $email_message="";
        }

        $calcredit = Credit::where("user_id",Auth::user()->unique_id)->where("type","calcr")->first();
        if(!empty($calcredit)){
            $cal_credit = $calcredit->balance;
            $cal_message = $calcredit->message;
        }else{
            $cal_credit = 0;
            $cal_message = "";
        }


        return response()->json([

            "sms_message" => $sms_message,
            "email_message" => $email_message,
            "call_message" => $cal_message,

            "smscr" => $sms_credit,
            "emcr" => $email_credit,
            "calcr" => $cal_credit
        ]);
    }


    public function returnUserTransactions(){
        $credit = Payment::where("user_id",Auth::user()->unique_id)->get();

        return response()->json([
            "status" =>true,
            "data" => $credit
        ]);

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
                $credit->message = $request->message;;
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
}
