<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{

    public function indexNonUnpaid(Request $request)
    {
        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min($perPage, 100));

        $orders = Order::with(['details.product', 'user'])
            ->where('state', '!=', OrderState::Unpaid)
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return OrderResource::collection($orders)
            ->additional([
                'meta' => [
                    'per_page' => $perPage,
                    'states_excluded' => [OrderState::Unpaid->value],
                ],
            ]);
    }

    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * POST /api/orders
     * El front manda:
     * {
     *   "user_id": 123,
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
                'items'   => 'required|array|min:1',
                'items.*.product_id'     => 'required|integer|exists:products,id',
                'items.*.quantity'       => 'required|integer|min:1',
                'items.*.unit_price'     => 'required|numeric|min:0',
                'items.*.unit_discount'  => 'nullable|integer|min:0',
                'expected_usdt_amount'   => 'nullable|numeric|min:0',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors'  => $e->errors(),
            ], 422);
        }

        $order = $this->orderService->createUnpaidOrder($data);

        Log::debug('Cobrando ' . $order->total_amount . ' Bs. como ' . $order->expected_usdt_amount . ' USDT.');

        return response()->json([
            'order_id'      => $order->id,
            'state'         => $order->state,
            'total_amount'  => $order->total_amount,
            'asset'         => $order->asset,
            'payment_instructions' => [
                'asset' => 'USDT',
                'usdt_address' => env('BINANCE_USDT_TRC20_ADDRESS'),
                'usdt_amount' => $order->expected_usdt_amount,
                'network' => env('BINANCE_NETWORK', 'TRX Tron (TRC20)'),
                'note' => 'Debes enviar exactamente este monto en esta misma moneda a traves de la red indicada.',
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