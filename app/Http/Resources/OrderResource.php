<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transforma un Order (con sus detalles) en array JSON.
     */
    public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'total_amount'    => $this->total_amount,
            'expected_usdt_amount'=> $this->expected_usdt_amount,
            'state'           => $this->state,
            'global_discount' => $this->global_discount,
            'paid_at'         => $this->paid_at,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
            //datos de usuario 
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id'    => $this->user?->id,
                    'first_name'  => $this->user?->first_name,
                    'f_last_name'  => $this->user?->f_last_name,
                    'phone' => $this->user?->phone,
                    'departamento' => $this->user?->departamento,                    
                ];
            }),
            // detalles de la orden:
            'details'         => OrderDetailResource::collection(
                $this->whenLoaded('details')
            ),
        ];
    }
}