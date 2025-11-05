<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibelulaTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'status',
        'monto',
        'identificador_deuda',
        'libelula_trans_id',
        'factura_url',
        'pasarela_url',
        'payload_snapshot',
        'amount_sent',
        'currency',
    ];

    /**
     * Una transacción pertenece a una Orden principal.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Una transacción pertenece a un Usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}