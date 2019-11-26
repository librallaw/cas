<?php

namespace App\Http\Controllers\Call;

use App\Call_group;
use App\Call_list;
use App\Call_log;
use App\Http\Resources\CallListResource;
use App\Http\Resources\CallLogResource;
use App\Http\Resources\SingleAttendanceResource;
use App\Personnel;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;
class CallListController extends Controller
{
    //

    public function createList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' =>'required',
            'idds' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message' => 'Sorry your reqistration could not be completed',
                'errors' =>$validator->errors()->all() ,
            ], 401);

            exit;
        }

        $newGroup = new Call_group();
        $newGroup->name = $request->name;
        $newGroup->status = 1;
        $newGroup->church_id = Auth::user()->unique_id;
        $newGroup->save();

        $idds = $request->idds;

        foreach ($idds as $idd){

            $call_list = new Call_list();
            $call_list -> member_id = $idd;
            $call_list -> call_group_id = $newGroup->id;
            $call_list -> church_id = Auth::user()->unique_id;
            $call_list -> save();

        }

        return response()->json([
            'status' => true,
            "message"=>"Call group successfully created",
            'data' => $newGroup->id
        ]);





    }


    public function showGroups()
    {

        $callgroups = Call_group::where("church_id",Auth::user()->unique_id)->orderBy('id','desc')->get();


        if(count($callgroups) > 0){
            return response()->json([
                'status' => true,
                "message"=>"Call group successfully returned",
                'data' => $callgroups
            ]);

        }else{
            return response()->json([
                'status' => false,
                "message"=>"No call group found in your church",
                'data' => $callgroups
            ]);
        }


    }


    public function showList($groupid)
    {

        $group = Call_group::where("id",$groupid)->where("church_id",Auth::user()->unique_id)->first();

        if(!empty($group)){

            $calllist  = Call_list::where('call_group_id',$groupid)->get();

            $data['group'] = $group;
            $data['members'] =  CallListResource::collection($calllist);

            $data['personnels'] = Personnel::where("owner_id",Auth::user()->unique_id)->where("type","callcenter")->get();

            return response()->json([
                'status' => true,
                "message"=>"Successfully loaded",
                'data' => $data
            ]);
        }else{

            return response()->json([
                'status' => false,
                "message"=>"You can ony view groups in your church",
                'data' => $group
            ]);
        }


    }


    public function assignList(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'idds' => 'required',
            'personnel' => 'required',
        ]);


        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message' => 'All fields are required',
                'errors' =>$validator->errors()->all() ,
            ], 401);
        }

        $check = User::where("id",$request->personnel)->where("owner_id",Auth::user()->unique_id)->first();

        if(!empty($check)){

            $assign = Call_list::whereIn('id', $request->idds)->update(['personnel' => $request->personnel]);


            return response()->json([
                'status'=>true,
                'message' => 'Members successfully Assigned',
            ] );

        }else{


            return response()->json([
                'status'=>false,
                'message' => 'You can only assign personnel in your church',
            ] );
        }






    }



     public function doCreatePersonnel(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'level' => 'required',
            'email' => 'required|unique:personnels',
        ]);

        if($validate->fails()){

            return response()->json([
                'status'=>false,
                'message' => 'All fieds are required',
                'errors' =>$validate->errors()->all() ,
            ], 401);

        }

        $personnel = new Personnel();

        $personnel->first_name = $request->first_name;
        $personnel->last_name = $request->last_name;
        $personnel->email = $request->email;
        $personnel->password = Hash::make("123456");
        $personnel->type = "callcenter";
        $personnel->owner_id = Auth::user()->unique_id;

        $personnel -> save();


        return response()->json([
            'status' => true,
            "message"=>"Personnel successfully created",
            'data' => $personnel
        ]);


    }


    public function addLog(Request $request)
    {

        $request->validate([
            'member_id' =>'required',
            'personnel' => 'required',
            'comment' => 'required',
            'call_group' => 'required',
            'flag' => 'required'
        ]);




        $newlog = new Call_log();

        $newlog->member_id = $request->member_id;
        $newlog->personnel = $request->personnel;
        $newlog->comment = $request->comment;
        $newlog->call_group_id = $request->call_group;
        $newlog->flag = $request->flag;
        $newlog->coming = $request->coming;

        $newlog ->save();


        $call_group = Call_list::find($request->call_id);
        $call_group -> status = 1;
        $call_group -> save();


        return response()->json([
            'status' => true,
            "message"=> "Report successfully logged",
            'data' => $newlog
        ]);


    }



    public function viewMemberCallLogs(Request $request)
    {

        $request->validate([
            'member_id' =>'required',
        ]);

        $call_log = Call_log::where("member_id",$request->member_id)->latest()->get();

        if($call_log ->count() > 0){

            return response()->json([
                'status' => true,
                "message"=> "Log(s) found",
                'data' => CallLogResource::collection($call_log)
            ]);

        }else{

            return response()->json([
                'status' => false,
                "message"=> "No log(s) found",
                'data' => $call_log
            ]);
        }




    }




    public function callReports($group_id)
    {

        $reports = Call_log::where("call_group_id",$group_id)->get();

        if(count($reports) > 0){
            return response()->json([
                'status' => true,
                "message"=>"No log found",
                'data' => []
            ]);
        }else{
            $data['reports'] =$reports;
            $data['name'] = $reports->name;

            return response()->json([
                'status' => true,
                "message"=>"Reports generated",
                'data' => $data
            ]);
        }



    }


    public function showPersonnels()
    {


        $personell = Personnel::where("owner_id",Auth::user()->unique_id)->where("type","callcenter")->get();


        if($personell->count() > 0)

        return response()->json([
            'status' => true,
            "message"=>"Prersonnel generated",
            'data' => $personell
        ]);

        else

            return response()->json([
                'status' => false,
                "message"=>"You do not have any personnel in your church",
                'data' => $personell
            ]);



    }


    public function showAlterPersonnel($userid)
    {
        $data['personnel'] = Personnel::find($userid);
        return view('callcenter.alterPersonnel',$data);
    }

    public function doAlterPersonnel(Request $request)
    {
        $request-> validate([
            'userid' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'level' => 'required',
            'church' => 'required',
            'email' => 'required',
        ]);

        $personnel = Personnel::find($request->userid);

        $personnel->first_name = $request->first_name;
        $personnel->last_name = $request->last_name;
        $personnel->email = $request->email;
        $personnel->church = $request->church;
        $personnel->level = implode('-',$request->level);

        $personnel -> save();


        return redirect()->back()->with("type","success")->with("message","Personnel successfully Altered");

    }



    public function callerList()
    {
        $activegroup = Call_group::where('status',1)->where("church_id",Auth::user()->owner_id)->get()->last();

        if(!empty($activegroup)){

            $data['call_list'] = CallListResource::collection(Call_list::where("personnel",Auth::user()->id)
                ->where('call_group_id',$activegroup->id)
                ->get());

            $data['call_group'] = $activegroup;


            return response()->json([
                'status' => true,
                "message"=>"Success",
                'data' => $data
            ]);
        }else{

            return response()->json([
                'status' => false,
                "message"=>"Group does not exist",
            ]);
        }




    }
}
