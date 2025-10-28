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
            'state'           => $this->state,
            'global_discount' => $this->global_discount,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,

            // detalles de la orden:
            'details'         => OrderDetailResource::collection(
                $this->whenLoaded('details')
            ),
        ];
    }
}