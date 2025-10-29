<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * POST /api/orders
     * El front manda:
     * {
     *   "user_id": 123,
     *   "asset": "USDT",
     *   "items": [
     *      { "product_id": 10, "quantity": 2, "unit_price": 4.50, "unit_discount": 0 },
     *      { "product_id": 12, "quantity": 1, "unit_price": 6.00 }
     *   ]
     * }
     *
     * Respuesta: order creada en estado UNPAID con total_amount calculado.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'asset'   => 'required|string|max:16',
                'items'   => 'required|array|min:1',
                'items.*.product_id'     => 'required|integer|exists:products,id',
                'items.*.quantity'       => 'required|integer|min:1',
                'items.*.unit_price'     => 'required|numeric|min:0',
                'items.*.unit_discount'  => 'nullable|integer|min:0',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors'  => $e->errors(),
            ], 422);
        }

        $order = $this->orderService->createUnpaidOrder($data);

        // Esta info se la muestras al usuario para que pague en Binance.
        // Ej: tu "cuenta destino", memo/tag que debe usar, etc.
        return response()->json([
            'order_id'      => $order->id,
            'state'         => $order->state,
            'total_amount'  => $order->total_amount,
            'asset'         => $order->asset,
            'payment_instructions' => [
                'type' => 'binance-transfer',
                // define esto en tu .env
                'binance_receiver' => env('BINANCE_RECEIVER_INFO', 'TU-CUENTA-BINANCE'),
                'note' => 'Debes enviar exactamente este monto en esta misma moneda',
            ],
        ], 201);
    }

    /**
     * GET /api/orders/{id}
     * El front puede preguntar estado actual de la orden.
     */
    public function show($id): JsonResponse
    {
        $order = $this->orderService->getOrderById($id);

        if (!$order) {
            return response()->json(['message' => 'Orden no encontrada'], 404);
        }

        return response()->json([
            'order_id'      => $order->id,
            'state'         => $order->state,
            'total_amount'  => $order->total_amount,
            'asset'         => $order->asset,
            'paid_at'       => $order->paid_at,
            'details'       => $order->details->map(function ($d) {
                return [
                    'product_id'     => $d->product_id,
                    'quantity'       => $d->quantity,
                    'subtotal_price' => $d->subtotal_price,
                    'unit_discount'  => $d->unit_discount,
                ];
            }),
        ]);
    }
}