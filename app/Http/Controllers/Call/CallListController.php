<?php

namespace App\Http\Controllers\Call;

use App\Call_group;
use App\Call_list;
use App\Call_log;
use App\Personnel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CallListController extends Controller
{
    //

    public function createList(Request $request)
    {
        $request->validate([
            'name' =>'required',
            'idds' => 'required'
        ]);

        $newGroup = new Call_group();
        $newGroup->name = $request->name;
        $newGroup->status = 1;
        $newGroup->save();

        $idds = explode(',', $request->idds);

        foreach ($idds as $idd){

            $call_list = new Call_list();
            $call_list -> user_id = $idd;
            $call_list -> call_group_id = $newGroup->id;
            $call_list -> save();

        }




        return response()->json([
            'status' => true,
            "message"=>"Personnel successfully created",
            'data' => $newGroup->id
        ]);



        return redirect('/call/group/'.$newGroup->id);



    }


    public function showGroups()
    {
        $data['callgroups'] = Call_group::orderBy('id','desc')->paginate(15);
        return view("callcenter.callgroups",$data);
    }


    public function showList($groupid)
    {
        $data['group'] = Call_group::find($groupid);
        $data['members'] = Call_list::where('call_group_id',$groupid)->get();

        $data['personnels'] = Personnel::all();

        return view("callcenter.callList",$data);
    }


    public function assignList(Request $request)
    {
        $request->validate([
           'idds' => 'required',
           'personnel' => 'required',
        ]);


        $assign = Call_list::whereIn('id', $request->idds)->update(['personnel' => $request->personnel]);

        return redirect()->back()->with("type","success")->with("message","Members successfully Assigned");


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
        $personnel->level = $request->level;
        $personnel->church = Auth::user()->unique_id;

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

        $newlog->user_id = $request->user_id;
        $newlog->personnel = $request->personnel;
        $newlog->comment = $request->comment;
        $newlog->call_group_id = $request->call_group;
        $newlog->flag = $request->flag;
        $newlog->coming = $request->coming;

        $newlog ->save();


        $call_group = Call_list::find($request->call_id);
        $call_group -> status = 1;
        $call_group -> save();


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
        return response()->json([
            'status' => true,
            "message"=>"Reports generated",
            'data' => Personnel::all()
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
}
