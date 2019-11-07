<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Service;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


use Validator;


class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Validate
        $validate = Validator::make($request->all(), [

            'church_id'     => Auth::user()->unique_id,
            'service_type'  => 'required'
        ]);

        if($validate->fails()){

            return response()->json(['error'=>$validate->errors()],'401');

        } else {
            $service = new Service();
            $service -> service_type  = $request -> service_type;
            $service -> church_id     = Auth::user()->unique_id;
            $service -> save();

            return response()->json([
                'status'    => true,
                'message'   => "Service created successfully",
                'service'    => $service
            ]);
        }

    }


    public function lastService()
    {
        $service = Service::where("church_id",Auth::user()->unique_id)->latest()->first();





        if(count($service) > 0) {


            return response()->json([
                'status' => true,
                'message' => "Successfully",
                'data' => $service->attendance,
                'attendance' => count($service->attendance)
            ]);
        }


        else {

            return response()->json([
                'status' => true,
                'message' => "No service found for this service",
                'data' => [],
                'attendance' => 0
            ]);

        }



    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function service_list() {

        if(isset($_GET['num'])){
            $services   =  Service::where("church_id",Auth::user()->unique_id)->take($_GET['num'])->get();
        } else {
            $services   =  Service::where("church_id",Auth::user()->unique_id)->get();
        }


        return response()->json([
            'status'    => true,
            'data'      => $services
        ]);
    }
}
