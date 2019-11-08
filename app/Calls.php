<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Calls extends Model
{
    //

    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'email',
        'church_name',
        'access'
    ];
}

