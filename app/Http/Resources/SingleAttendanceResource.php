<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SingleAttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
         return [

        'full_name' => $this->member->full_name,
        'member_id' => $this->member_id,
        'phone_number' => $this->member->phone_number,
        'arrival_time' => $this->arrival_time,
        'group' => $this->group,
        'level' => $this->level,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
    ];
    }
}


