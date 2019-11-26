<?php

namespace App\Http\Controllers;

use App\Model\Cart;
use App\Model\Order;
use App\Model\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use Unicodeveloper\Paystack\Facades\Paystack;

class PaymentController extends Controller
{


    public function confirm_payment(Request $request)
    {



        $curl = curl_init();
        $reference = isset($_GET['reference']) ? $_GET['reference'] : '';
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

            $credit = $request -> credit;
            $type = $request -> type;


            //check for the amount of credit for package
            $money_payed = $tranx->data->amount;

            switch ($type){
                case emcr: $per_credit = 10;
                break;

                case smscr: $per_credit = 5;
                    break;

                case calcr: $per_credit = 5;
                    break;

            }


            $legal_credit = $money_payed / $per_credit;


            //check for fradulent transaction

            if($legal_credit != $credit){

                return response()->json([
                    'status' => true,
                    'message' => "There is an issue with your transaction code: 9821"
                ]);
            }else{

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



