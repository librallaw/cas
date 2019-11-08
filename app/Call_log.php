<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Call_log extends Model
{
    //

//    public function getDateFormat()
//    {
//        return 'Y-m-d H:i:s.u';
//    }

    public function user()
    {
        return $this->hasOne("App\User",'id','user_id');
    }


    public function caller()
    {
        return $this->hasOne("App\Personnel",'id','personnel');
    }


    public function group()
    {
        return $this->hasOne("App\Call_group",'id','call_group_id');
    }



}
