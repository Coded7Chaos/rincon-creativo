<!DOCTYPE html>
<html>
<head>
    <title>Confirmación de Pedido</title>
</head>
<body>
    <h1>¡Gracias por tu compra!</h1>
    <p>
        Hola {{ $order->user->name }},
    </p>
    <p>
        Hemos recibido el pago de tu pedido #{{ $order->id }}.
        Tu pedido ha sido marcado como 'Pagado' y está siendo procesado.
    </p>
    <p>
        Total pagado: ${{ number_format($order->total_amount, 2) }}
    </p>
    <p>
        Gracias,<br>
        {{ config('app.name') }}
    </p>
</body>
</html>