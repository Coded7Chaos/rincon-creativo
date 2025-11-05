<?php

namespace App\Mail;

use App\Models\Order; // <-- IMPORTA LA ORDEN
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPaidConfirmation extends Mailable implements ShouldQueue // <-- Hacemos que use la cola
{
    use Queueable, SerializesModels;

    public Order $order; // <-- Hacemos pública la orden

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order) // <-- Recibe la orden
    {
        $this->order = $order;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            // Asunto del correo (usando el email del usuario de la orden)
            subject: 'Confirmación de tu Pedido #' . $this->order->id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Apunta a una vista que crearemos (ej. 'emails.orders.paid')
        return new Content(
            view: 'emails.orders.paid',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}