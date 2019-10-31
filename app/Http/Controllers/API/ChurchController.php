<?php

namespace App\Http\Controllers;

use App\church;
use Illuminate\Http\Request;

class ChurchController extends Controller
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
        $validatedData = $request->validate([
            'church_name'        =>'required|max:100',
            'church_address'    => 'church_address|required',
            'church_phone'      => 'church_phone|required',
            'church_email'      => 'church_email|required'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\church  $church
     * @return \Illuminate\Http\Response
     */
    public function show(church $church)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\church  $church
     * @return \Illuminate\Http\Response
     */
    public function edit(church $church)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\church  $church
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, church $church)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\church  $church
     * @return \Illuminate\Http\Response
     */
    public function destroy(church $church)
    {
        //
    }
}
