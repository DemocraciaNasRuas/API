<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class State extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'state_id' => $this->id,
            'state_code' => $this->state_code,
            'state' => $this->state,
            'cities' => City::collection($this->whenLoaded('cities'))
        ];
    }
}
