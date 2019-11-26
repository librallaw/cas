<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Call_list extends Model
{
    //

//    public function getDateFormat()
//    {
//        return 'Y-m-d H:i:s.u';
//    }

    public function member()
    {
        return $this->hasOne(Members::class,'id','member_id');
    }

    public function caller()
    {
        return $this->hasOne(User::class,'id','personnel');
    }
}
