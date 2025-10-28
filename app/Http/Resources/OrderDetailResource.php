<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    /**
     * Transforma un OrderDetail en array JSON.
     */
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'product_id'     => $this->product_id,
            'quantity'       => $this->quantity,
            'subtotal_price' => $this->subtotal_price,
            'unit_discount'  => $this->unit_discount,

            // Info básica del producto (solo si está cargado la relación 'product')
            'product'        => $this->whenLoaded('product', function () {
                return [
                    'id'    => $this->product?->id,
                    'name'  => $this->product?->name ?? null,
                    'price' => $this->product?->price ?? null,
                    // agrega otros campos que sean relevantes de tu modelo Product, ej. sku, description...
                ];
            }),
        ];
    }
}