<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CallLogResource extends JsonResource
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
            "id" => $this->id,
            "member_id"=> $this->member_id,
            'full_name' => $this->member->full_name,
            "comment"=> $this->comment,
            'personnel' => (isset($this->caller)?$this->caller->first_name." ".$this->caller->last_name:null),
            "call_group_id"=>$this->call_group_id,
            "flag"=> $this->flag,
            "coming"=> $this->coming,
            "created_at"=> $this->created_at->diffForHumans(),
            "updated_at"=> $this->updated_at->diffForHumans()
        ];
    }
}
