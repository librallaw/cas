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

        return response()->json([
            'status' => true,
            'data' => $leaders,
        ]);
    }
}
