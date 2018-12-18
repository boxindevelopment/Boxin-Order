<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray($request)
    {
        $url = 'https://boxin-prod-webbackend.azurewebsites.net/';

        $data = [
            'id'                => $this->id,
            'name'              => $this->name,
            'image'             => is_null($this->image) ? null : $url.'images/banner'.'/'.$this->image,
            'status'            => new StatusResource($this->status),
        ];

        return $data;
    }
}
