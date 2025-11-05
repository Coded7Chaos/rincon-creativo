<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Mail\OrderPaidConfirmation; // <-- IMPORTA EL MAILABLE
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail; // <-- IMPORTA LA FACHADA DE MAIL

class SendOrderPaidConfirmation
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderPaid $event): void
    {
        // Obtiene el email del usuario asociado a la orden
        $customerEmail = $event->order->user->email;

        // Envía el correo usando el Mailable que creamos
        Mail::to($customerEmail)->send(new OrderPaidConfirmation($event->order));
    }
}