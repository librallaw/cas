<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CallListResource extends JsonResource
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
            'id' => $this->id,
            'member_id' => $this->member_id,
            'full_name' => $this->member->full_name,
            'call_group_id' => $this->call_group_id,
            'phone_number' => $this->member->phone_number,
            'personnel' => (isset($this->caller)?$this->caller->first_name." ".$this->caller->last_name:null),
            'church_id' => $this->church_id,
            'flag' => $this->flag,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
