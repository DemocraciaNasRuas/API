<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Event extends Resource
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
            'event_id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'date' => $this->date,
            'user' => User::make($this->whenLoaded('user')),
            'address' => Address::make($this->whenLoaded('address'))
        ];
    }
}
