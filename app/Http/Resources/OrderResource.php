<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (!is_null($this->user_id)) {
            $user = [
                'id'        => $this->user->id,
                'first_name'=> $this->user->first_name,
                'last_name' => $this->user->last_name,
                'email'     => $this->user->email,
                'phone'     => $this->user->phone,
            ];
        }

        if (!is_null($this->space_id)) {
            $space = [
                'id'    => $this->space->id,
                'name'  => $this->space->name,
            ];
        }

        $data = [
            'id'        => $this->id,
            'total'     => $this->total,
            'user'      => $user,
            'order_date'=> $this->created_at->format('Y-m-d'),
            'status'    => $this->status->name,
            'space'     => $space,
        ];

        return $data;
    }
}
