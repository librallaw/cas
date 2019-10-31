<?php
/**
 * File       : UserController
 * @Auhor     : Folake Giwa
 * @email     : folakegiwa@loveworld360.com
 * @kingschat : +2348064710767
 * Date: 10/22/19
 * Time: 14:23
 */

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Validator;
class UserController extends Controller
{
    public $successStatus = 200;
    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(){
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
            $user = Auth::user();
            $success['token'] =  $user->createToken('MyApp')-> accessToken;
            return response()->json(['success' => $success], $this-> successStatus);
        }
        else{
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name'     => 'required',
            'email'         => 'required|email|unique:users',
            'church_name'   => 'required',
            'password'      => 'required',
            'c_password'    => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }
        //$input = $request->all();
        $user = new User();
        $user->full_name    = $request->full_name;
        $user->email        = $request->email;
        $user->church_name  = $request->church_name;
        $user->password     = bcrypt($request->password);
        $user->unique_id    = bcrypt($request->email);
        $user->save();

        //$input['password'] = bcrypt($input['password']);
        //$input['unique_id'] = bcrypt($input['email']);


       // $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')-> accessToken;
        $success['name'] =  $user->name;
        return response()->json([
            'status'=>true,
            'message'=> "Registration successful.",
            'token'=>$user->createToken('MyApp')-> accessToken,
            'user'=>$user]);
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
            'data'      => Auth::User()
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