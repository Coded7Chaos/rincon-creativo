<?php

namespace App\Events;

use App\Models\Order; // <-- IMPORTA TU MODELO DE ORDEN
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * La orden que fue pagada.
     */
    public Order $order; // <-- HAZ PÚBLICA LA ORDEN

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order) // <-- RECIBE LA ORDEN
    {
        $this->order = $order;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}