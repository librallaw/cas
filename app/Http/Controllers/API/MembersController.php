<?php



namespace App\Http\Controllers\API;

use App\Attendance;
use App\Imports\CsvImport;
use App\Service;
use Illuminate\Http\Request;
use App\Members;
use App\Http\Controllers\Controller;


use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;

use Validator;


class MembersController extends Controller
{
    public $successStatus = 200;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //

        $members = Members::all();
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
        //validate
        $validator = Validator::make($request->all(), [
            'title'             => 'required',
            'full_name'         => 'required',
            'gender'            => 'required',
            'birth_date'        => 'required',
            'phone_number'      => 'required',
            'email'             => 'required|email',
            'marital_status'    => 'required',
            'group_assigned'    => 'required',
            'home_address'      => 'required'
        ]);
        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message' => 'Sorry your reqistration could not be completed',
                'errors' =>$validator->errors()->all() ,
            ], 401);
        } else {
            $mem = new Members();
            $mem->title             = $request->title;
            $mem->full_name         = $request->full_name;
            $mem->gender            = $request->gender;
            $mem->birth_date        = $request->birth_date;
            $mem->phone_number      = $request->phone_number;
            $mem->email             = $request->email;
            $mem->marital_status    = $request->marital_status;
            $mem->group_assigned    = $request->group_assigned;
            $mem->home_address      = $request->home_address;
            $mem->church_id         = Auth::user()->unique_id;
            $mem->save();
            //$mem = Members::create($request->all());

            $success['full_name'] =  $mem->full_name;

            return response()->json([
                'status'    => true,
                'message'   => "Members successfully created.",
                'member'    => $mem
            ]);

        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\members  $members
     * @return \Illuminate\Http\Response
     */
    public function show(members $members)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\members  $members
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        //validate


        $id = $_POST['id'];

        $member = Members::find($id);

        $member -> full_name = "Zinani Chuks";
        $member -> save();

        return response()->json([

            'status'=>true,
            'message' => 'User data successfully updated'

        ]);



       // dd($member);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\members  $members
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, members $members)
    {
        //
        $request->validate([
            'church_id'         => 'required',
            'title'              => 'required',
            'full_name'         => 'required',
            'gender'            => 'required',
            'birth_date'        => 'required',
            'phone_number'      => 'required',
            'email'             => 'required|email',
            'marital_status'    => 'required',
            'group_assigned'    => 'required',
            'home_address'      => 'required'
        ]);

        $member = Members::find($members);
        $member->title          = $request->get('title');
        $member->full_name      = $request->get('full_name');
        $member->gender         = $request->get('gender');
        $member->birth_date     = $request->get('birth_date');
        $member->phone_number   = $request->get('phone_number');
        $member->email          = $request->get('email');
        $member->marital_status = $request->get('marital_status');
        $member->group_assigned = $request->get('group_assigned');
        $member->home_address   = $request->get('home_address');
        $member->save();
    }

    public function bulkUpload(Request $request) {




        if($request->hasFile('file')) {

            $path = $request->file('file');

            return response()->json([
                'status'    => true,
                'message'   => $_POST,
            ]);





            //Excel::import(new CsvImport, request()->file('file'));
            $import = new CsvImport();
            $import->import($request->file('file'));

            foreach ($import->failures() as $failure) {
                $failure->row(); // row that went wrong
                $failure->attribute(); // either heading key (if using heading row concern) or column index
                $failure->errors(); // Actual error messages from Laravel validator
                $failure->values(); // The values of the row that has failed.
            }

             // dd($import);

                    return response()->json([
                        'status'    => true,
                        'message'   => "Members successfully created.",
                    ]);
           }

    }


    //This method fetch all members record from db
    public function lists() {

        if(isset($_GET['num'])){
            $members   =  Members::where("church_id",Auth::user()->unique_id)->take($_GET['num'])->get();
        }else{
            $members   =  Members::where("church_id",Auth::user()->unique_id)->get();
        }

        return response()->json([
            'status'    => true,
            'data'      => $members
        ]);
    }



    public function export()
    {
        return Excel::download(new MembersExport, 'members.xlsx');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\members  $members
     * @return \Illuminate\Http\Response
     */
    public function destroy(members $members)
    {
        //
    }

    public function groups(){

        $grp_array = array();
        $grp_array2 = array();
        $a = Members::select(['group_assigned','full_name'])->where("church_id",Auth::user()->unique_id)->groupBy
        (['group_assigned','full_name'])->get();



        if($a){

            foreach ($a as $item) {
                $grp_array[] = $item->group_assigned;
            }


            foreach ($grp_array as $grp) {
                $grp_array2[] = array(
                    'group' => $grp,
                    'leader' => (Members::where("group_assigned",$grp)->where("level",1)->first() !=null?
                        Members::where("group_assigned",$grp)->where("level",1)->first()->full_name : ""),
                );
            }

            return response()->json([
                'status'    => true,
                'data'      => $grp_array2
            ]);
        }else{
            return response()->json([
                'status'    => false,
                'message'   => 'No record found.',
                'data' => []
            ]);
        }
    }

    public function active(){

            $active = Members::where('church_id',Auth::user()->unique_id)->where('active',1)->get();
            if($active){

                return response()->json([
                    'status'    => true,
                    'data'      => $active
                ]);
            } else{
                return response()->json([
                    'status'    => false,
                    'message'   => 'No active members found.'
                ]);
            }



    }

    public function in_active(){

        $active = Members::where('church_id',Auth::user()->unique_id)->where('active',0)->get();
        if($active){

            return response()->json([
                'status'    => true,
                'data'      => $active
            ]);
        } else{
            return response()->json([
                'status'    => false,
                'message'   => 'No active members found.',
                'data' => []
            ]);
        }

    }


    public function memberTracking($user_id)
    {

        $services = Service::where("church_id",Auth::user()->unique_id)->latest()->take(15)->get();

        if(count($services) > 0) {



                $track = array();
                $x = 0;
                $present = 0;
                $absent = 0;
                foreach ($services as $service) {

                    $serve = Attendance::where("member_id", $user_id)->where("service_date", $service->service_date)->get();

                    $track[$x]['service_date'] = $service->service_date;

                    if (count($serve) > 0) {
                        $track[$x]['status'] = 1;
                        $track[$x]['type'] = "success";
                        $track[$x]['background'] = "green";
                        $present++;
                    } else {
                        $track[$x]['status'] = 0;
                        $track[$x]['type'] = "danger";
                        $track[$x]['background'] = "red";
                        $absent++;
                    }

                    $track[$x]['date'] = $service->date;

                    $x++;
                }

                // dd($present);
                $data['present'] = $present;
                $data['absent'] = $absent;
                $data['tracks'] = $track;
                $data['user'] = Members::where('id', $user_id)->first();

                return response()->json([

                    'status' => true,
                    "message" => "success",
                    "data" => $data
                ]);



        }else{
            return response()->json([

                'status' => false,
                "message" => "No service founnd in your account",
                'data' => []
            ]);
        }

    }


    public function memberProfile($user_id)
    {
        $user = Members::where('id', $user_id)->where("church_id", Auth::user()->unique_id)->first();

        if(!empty($user)) {

            return response()->json([

                'status' => true,
                "message" => "success",
                'data' => $user
            ]);

        }else{

            return response()->json([

                'status' => false,
                "message" => "You are not authorized to access this resource",
                'data' => $user
            ]);
        }
    }

    public function editProfile(Request $request, Members $id){

        $request->validate([
            'church_id'         => 'required',
            'title'             => 'required',
            'full_name'         => 'required',
            'gender'            => 'required',
            'birth_date'        => 'required',
            'phone_number'      => 'required',
            'email'             => 'required|email',
            'marital_status'    => 'required',
            'group_assigned'    => 'required',
            'home_address'      => 'required'
        ]);

        //$mem_id = $_POST['id'];
       //dd($mem_id);



       // dd($member);

        $id -> church_id        = Auth::user()->unique_id;
        $id -> title            = $request->get('title');
        $id -> full_name        = $request->get('full_name');
        $id -> gender           = $request->get('gender');
        $id -> birth_date       = $request->get('birth_date');
        $id -> phone_number     = $request->get('phone_number');
        $id -> email            = $request->get('email');
        $id -> marital_status   = $request->get('marital_status');
        $id -> group_assigned   = $request->get('group_assigned');
        $id -> home_address     = $request->get('home_address');
        $id -> save();

        return response()->json([
            'status'    => true,
            'message'   => 'Profile successfully updated',
            'data'      =>$id

        ]);

    }




}
