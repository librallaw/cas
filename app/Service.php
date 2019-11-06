<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    //
    protected $fillable = [
        'id',
        'church_id',
        'service_type',
    ];

    public function attendance()
    {
       // return $this->hasMany(Service::class,)
    }
}
