<?php

namespace App\Http\Controllers\API;

use App\Members;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LeadersController extends Controller
{
    public function leaders(){

        $leaders = Members::where('level', 1)->get();

        return response()->json([
            'status' => true,
            'data' => $leaders,
        ]);
    }
}
