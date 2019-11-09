<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{

    protected $table = "users";
    //

//    public function getDateFormat()
//    {
//        return 'Y-m-d H:i:s.u';
//    }


    public function user()
    {
        return $this->hasOne("App\User",'id','user_id');
    }


    public function full_name()
    {

        return ucfirst(strtolower($this->first_name))." ".ucfirst(strtolower($this->last_name));
    }
}
