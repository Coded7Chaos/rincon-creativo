<?php

namespace App\Models;


use App\Enums\OrderState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\OrderDetail;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'total_amount',
        'state',
        'global_discount',
        'asset',
        'paid_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2', 
        'global_discount' => 'integer',
        'state' => OrderState::class,
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
        
    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function markAsPaid(int $binanceTimestampMs = null): void
    {
        $this->state = OrderState::Pending;
        $this->paid_at = $binanceTimestampMs ? now()->setTimestampMs($binanceTimestampMs) : now();

        $this->save();
    }

}
