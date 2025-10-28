<?php

namespace App\Models;


use App\Enums\OrderState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\OrderDetail;

class order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'total_amount',
        'state',
        'global_discount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2', 
        'global_discount' => 'integer',
        'state' => OrderState::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }
}
