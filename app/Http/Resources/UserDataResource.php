<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDataResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     */
    public function toArray(Request $request)
    {
        // "this" se refiere al objeto User que le pasaste
        return [
            'id'            => $this->id,
            'first_name'    => $this->first_name,
            'f_last_name'   => $this->f_last_name,
            's_last_name'   => $this->s_last_name,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'departamento'  => $this->departamento,
            'city'          => $this->city,
            'address'       => $this->address,
            'photo_url'     => $this->photo_url,
            // métrica rápida útil
            'orders_count' => $this->whenLoaded('orders', function () {  return $this->orders->count(); }),
            // Incluimos también las órdenes, formateadas con otro Resource:
            'orders'        => OrderResource::collection($this->whenLoaded('orders')),
        ];
    }
}