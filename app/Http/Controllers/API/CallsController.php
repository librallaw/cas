<?php

namespace App\Http\Controllers\API;

use App\Calls;
use Illuminate\Http\Request;
use Validator;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


class CallsController extends Controller
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
        //
        //validate
        $validator = Validator::make($request->all(), [
            'first_name'        => 'required',
            'last_name'         => 'required',
            'email'             => 'required|email|unique:users',
            'church_name'       => 'required',
        ]);
        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message' => 'Sorry your reqistration could not be completed',
                'errors' =>$validator->errors()->all() ,
            ], 401);
        } else {
            $call = new Calls();
            $call->first_name           = $request->first_name;
            $call->last_name            = $request->last_name;
            $call->email                = $request->email;
            $call->church_name          = $request->church_name;
            $call->access               = $request->access;
            $call->save();

            return response()->json([
                'status'    => true,
                'message'   => "Members successfully created.",
                'member'    => $call
            ]);

        }


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\calls  $fr
     * @return \Illuminate\Http\Response
     */
    public function show(calls $fr)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\calls  $fr
     * @return \Illuminate\Http\Response
     */
    public function edit(calls $fr)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\calls  $fr
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, calls $fr)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\calls  $fr
     * @return \Illuminate\Http\Response
     */
    public function destroy(calls $fr)
    {
        //
    }
}
