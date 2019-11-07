<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    //
    protected $table = 'attendance';


    protected $fillable = [
        'church_id',
        'member_id',
        'arrival_time',
        'service_date',
        'service_type',
    ];


    public function member()
    {
        return $this->belongsTo(Members::class,'member_id', 'id');
    }
}

