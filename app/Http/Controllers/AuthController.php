<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class AuthController extends Controller
{
    //

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'full_name'     => 'required',
            'email'         => 'required|email|unique:users',
            'church_name'   => 'required',
            'password'      => 'required',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'status'=>false,
                'message' => 'Sorry your reqistration could not be completed',
                'errors' =>$validator->errors()->all() ,
            ], 401);


        }


        //disable mass assignment protection.
        //please only use this if you know what u are doing, it is dangerous

        User::unguard();

        $user = new User();
        $user->full_name    = $request->full_name;
        $user->email        = $request->email;
        $user->church_name  = $request->church_name;
        $user->password     = bcrypt($request->password);
        $user->unique_id    = bcrypt($request->email);

        $user->save();

        User::reguard();

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        return response()->json([
            'status'        => true,
            'message'       => 'Successfully created user!',
            'data'          => $user,
            'access_token'  => "Bearer ".$tokenResult->accessToken,
            'token_type'    => 'Bearer',
            'user'          => Auth::user(),
            'expires_at'    => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()

        ], 201);
    }


    public function login(Request $request){

        $validator = Validator::make($request->all(), [
            'email'     => 'required|string|email',
            'password'  => 'required|string',
        ]);



        if ($validator->fails()) {

            $errors =$validator->errors()->all();

            return response()->json([
                'status'=> false,
                'message' => 'Some error(s) occurred',
                'errors'=> $errors

            ]);

        }


        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){

            $user = $request->user();

            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->token;

            if ($request->remember_me)
                $token->expires_at = Carbon::now()->addWeeks(1);
            $token->save();

            return response()->json([
                'status' => true,
                'message' => "success",
                'access_token' => "Bearer ".$tokenResult->accessToken,
                'token_type' => 'Bearer',
                'user' => Auth::user(),
                'expires_at' => Carbon::parse(
                    $tokenResult->token->expires_at
                )->toDateTimeString()
            ]);


        }
        else{
            return response()->json([
                'status'=> false,
                'type'=> 'danger',
                'message' => 'Invalid UserName or Password',

            ]);
        }
    }


    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => true,
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {

        return response()->json([
            'status'=> true,
            'user' => $request->user()]);

    }

    /**
     * details api
     *
     * @return \Illuminate\Http\Response
     */
    public function details()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this-> successStatus);
    }

    public function profile()
    {
        return response()->json([
            'status'    => true,
            'data'      => Auth::user()
        ]);
    }

    public function updatePassword(request $request){

        if (!(Hash::check($request->get('old_password'), Auth::user()->password))) {

            return response()->json(['errors' => ['current'=> ['Current password does not match']]], 422);
        }

        if(strcmp($request->get('old_password'), $request->get('new_password')) == 0){

            return response()->json(['errors' => ['current'=> ['New Password cannot be same as your current password']]], 422);
        }
        $validatedData = $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);
        //Change Password
        $user = Auth::User();
        $user->password = Hash::make($request->get('new_password'));

        $user->save();

        return response()->json([
            'status'    => true,
            'user'      => $user,
            'message'   => 'Password updated successfully'
        ]);
    }
}
