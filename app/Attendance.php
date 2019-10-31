<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    //

    protected $fillable = [
        'id',
        'church_id',
        'member_id',
        'arrival_time',
        'service_date',
        'service_type'
    ];
}

