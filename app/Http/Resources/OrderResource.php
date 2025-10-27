<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     */
    public function toArray(Request $request): array
    {
        // "this" se refiere al objeto Order que le pasaste
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'f_last_name' => $this->f_last_name,
            's_last_name' => $this->s_last_name,
            'email' => $this->email,
            'rol' => $this->role,
            'registrado_el' => $this->created_at->format('d-m-Y'),
        ];
    }
}