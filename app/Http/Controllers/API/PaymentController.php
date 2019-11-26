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


            if(count($reference2) < 1){

                die("Reference does not exist");

            }

            //Check if value has already been given to the user
            if(!empty($reference2->gateway_response)){
                //  dd($reference2->status);

                die("Value already given for thus transaction");

            }

            $per_credit = 10;

            $amount = $request -> amount;



            $activi = OrderItem::whereIn('id', $items_array)->update(['status' => 1]);


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



