<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Address extends Resource
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
            'address_id' => $this->id,
            'street' => $this->street,
            'number' => $this->number,
            'district' => $this->district,
            'postcode' => $this->postcode,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude
        ];
    }
}
