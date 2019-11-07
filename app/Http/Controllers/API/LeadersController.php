<?php

namespace App\Http\Controllers\API;

use App\Members;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LeadersController extends Controller
{
    public function leaders(){

        $leaders = Members::where('level', 1)->where("church_id",Auth::user()->unique_id)->get();

        if(count($leaders) > 0)

        return response()->json([
            'status' => true,
            'data' => $leaders,
        ]);

        else
            return response()->json([
                'status' => false,
                'message' => "No leader found in your church",
                'data' => $leaders,
            ]);

    }
}
