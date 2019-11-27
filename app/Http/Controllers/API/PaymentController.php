<?php

namespace App\Http\Controllers\API;

use App\Credit;
use App\Model\Cart;
use App\Model\Order;
use App\Model\OrderItem;
use App\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;

use Unicodeveloper\Paystack\Facades\Paystack;

class PaymentController extends Controller
{


    public function confirm_payment(Request $request)
    {



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


            $reference2 = Payment::where('reference',$unique_id)->first();


            //Check if value has already been given to the user
            if(count($reference2) > 0){

                return response()->json([
                    'status' => false,
                    'message' => "Value already given for thus transaction"
                ],400);


            }

            $credit = $request -> amount;
            $type = $request -> type;


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

                $credit =  Credit::where("user_id",Auth::user()->user_id)->first();

                if(!empty($credit)){
                    $credit->balance = $credit->balance + $legal_credit;
                    $credit->save();

                }else{

                    $newCredit = new Credit();
                    $newCredit->balance = $legal_credit;
                    $newCredit -> save();

                }
                //credit the user's sms balance

                return response()->json([
                    'status' => true,
                    'legal_credit' => $legal_credit,
                    'per_credit' => $per_credit,
                ]);

                //credit the user

                echo "we are ready to credit you";
            }




            return response()->json([
                'status' => true,
                'message' => "Payment successfully"
            ]);


        } else {

            $order = Order::where("unique_id", $tranx->data->reference)->first();
            $order->status = 0;
            $order->save();


            return response()->json([
                'status' => false,
                'message' => "Sorry there an error occurred while processing your transaction, please try again later"
            ]);


        }
    }
}



