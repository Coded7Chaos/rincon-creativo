<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Enums\OrderState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{

    public function createUnpaidOrder(array $payload): Order
    {
        // payload esperado:
        // [
        //   'user_id' => int,
        //   'asset' => 'USDT',
        //   'items' => [
        //      [
        //        'product_id' => 12,
        //        'quantity' => 2,
        //        'unit_price' => 4.50,
        //        'unit_discount' => 0
        //      ],
        //      ...
        //   ]
        // ]

        return DB::transaction(function () use ($payload) {
            $total = 0;

            // 1. Creamos la orden en estado UNPAID sin total aún
            $order = Order::create([
                'user_id'         => $payload['user_id'],
                'state'           => OrderState::Unpaid,
                'global_discount' => 0,
                'total_amount'    => 0,
                'asset'           => 'USDT',
                'paid_at'         => null,
            ]);

            // 2. Insertamos cada detalle
            foreach ($payload['items'] as $item) {
                $quantity = (int)$item['quantity'];
                $unitPrice = (float)$item['unit_price'];
                $unitDiscount = isset($item['unit_discount']) ? (int)$item['unit_discount'] : 0;

                $subtotal = $quantity * $unitPrice - $unitDiscount;
                if ($subtotal < 0) {$subtotal = 0;}

                $total += $subtotal;

                OrderDetail::create([
                    'order_id'       => $order->id,
                    'product_id'     => $item['product_id'],
                    'quantity'       => $quantity,
                    'subtotal_price' => $subtotal,
                    'unit_discount'  => $unitDiscount,
                ]);
            }

            // 3. Actualizamos el total real en la orden
            $order->total_amount = $total;
            $order->save();

            return $order;
        });
    }

    /**
     * Llamado cuando Binance manda un balanceUpdate con d > 0.
     * Intenta encontrar una orden UNPAID con:
     *   - misma moneda (asset)
     *   - mismo monto total (match tolerante)
     * Si la encuentra, la marca como pagada.
     */
    public function matchIncomingDeposit(float $deltaAmount, string $asset, int $binanceTimestampMs): ?Order
    {
        // Traemos TODAS las que siguen UNPAID en ese asset
        $candidates = Order::where('state', OrderState::Unpaid)
            ->where('asset', 'USDT')
            ->get();

        if ($candidates->isEmpty()) {
            Log::info("[OrderService] No hay órdenes sin pagar en asset {$asset}");
            return null;
        }

        $match = $candidates->first(function (Order $o) use ($deltaAmount) {
            return $this->amountsAreEqual(
                (float)$o->total_amount,
                $deltaAmount
            );
        });

        if (!$match) {
            Log::info("[OrderService] Ninguna orden coincide con monto {$deltaAmount} {$asset}");
            return null;
        }

        // Marcamos pagada
        $match->markAsPaid($binanceTimestampMs);

        Log::info("[OrderService] Orden {$match->id} marcada como PAGADA ({$deltaAmount} {$asset})");

        // Aquí podrías: event(new OrderPaid($match));
        return $match;
    }

    /**
     * Comparación tolerante para evitar problemas de 10.0000001 vs 10.00
     */
    protected function amountsAreEqual(float $a, float $b): bool
    {
        return round($a, 2) === round($b, 2);
    }

    /**
     * Para el GET /orders/{id}
     */
    public function getOrderById($orderId): ?Order
    {
        return Order::with('details')->find($orderId);
    }
}