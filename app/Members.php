<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Members extends Model
{
    //Fillable

    protected $fillable = [
            'church_id',
            'title',
            'full_name',
            'gender',
            'birth_date',
            'phone_number',
            'email',
            'marital_status',
            'group_assigned',
            'home_address',
            'first_timer'
    ];

}
