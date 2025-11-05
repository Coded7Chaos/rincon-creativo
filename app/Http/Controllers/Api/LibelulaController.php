<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // <-- Asegúrate que Http esté importado
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\LibelulaTransaction;
use Illuminate\Http\Client\PendingRequest; // <-- NUEVA importación para el tipo
use App\Events\OrderPaid; // <-- IMPORTAR EL EVENTO

class LibelulaController extends Controller
{
    // --- INICIO: NUEVO MÉTODO (Punto 4) ---
    /**
     * Crea una instancia del cliente HTTP con reintentos y timeout.
     */
    private function libelula(): PendingRequest
    {
        return Http::timeout(20)      // Timeout de 20 segundos
                   ->retry(3, 200); // 3 reintentos, 200ms de espera entre fallos
    }
    // --- FIN: NUEVO MÉTODO ---

    /**
     * Inicia el cobro: crea transacción local, registra deuda en Libélula y devuelve url_pasarela_pagos.
     */
    public function registrarDeuda(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::with(['user', 'details.product'])
                    ->where('user_id', $request->user()->id) 
                    ->findOrFail($data['order_id']);

        $customerEmail = optional($order->user)->email;
        if (!$customerEmail) {
            return response()->json(['message' => 'La orden no tiene usuario/email asociado.'], 422);
        }

        if ($order->state === 'paid') {
             return response()->json(['message' => 'Este pedido ya fue pagado.'], 409);
        }

        // --- INICIO: NUEVA LÓGICA (Punto 1c) ---
        /**
         * Reutiliza una transacción pendiente si ya existe para esta orden.
         * Esto evita crear múltiples deudas si el cliente refresca la página.
         */
        $existing = LibelulaTransaction::where('order_id', $order->id)
            ->where('status', 'PENDIENTE')
            ->latest('id')
            ->first();

        if ($existing && $existing->pasarela_url) {
            // Si ya tenemos una URL de pasarela, la devolvemos
            return response()->json([
                'url_pasarela_pagos' => $existing->pasarela_url,
                'transaction_id'     => $existing->libelula_trans_id,
                'reused'             => true, // (Opcional, para depuración)
            ]);
        }
        // --- FIN: NUEVA LÓGICA ---

        // (Si no hay transacción pendiente, continuamos creando una nueva)

        $sumLineas = $order->details->sum('subtotal_price'); 
        $globalPercent = (int) $order->global_discount;      
        $descuentoGlobalMonto = round(($sumLineas * $globalPercent) / 100, 2);

        $transaction = LibelulaTransaction::create([
            'order_id'  => $order->id,
            'user_id'   => $request->user()->id, 
            'status'    => 'PENDIENTE',
            'monto'     => $order->total_amount,
        ]);

        $identificador = 'LTXN-'.$transaction->id.'-'.Str::ulid();
        $transaction->update(['identificador_deuda' => $identificador]);

        $lineas = $order->details->map(function($d) {
            $qty = max(1, (int)$d->quantity);
            $unit = $qty > 0 ? round($d->subtotal_price / $qty, 2) : (float)$d->subtotal_price;

            return [
                'concepto' => optional($d->product)->nombre ?? ('Item #'.$d->id),
                'cantidad' => $qty,
                'costo_unitario' => $unit,
                'descuento_unitario' => 0, 
            ];
        })->values()->all();

        $callbackUrl = route('webhook.libelula.exitoso'); 
        $urlRetorno  = rtrim(config('app.frontend_url', 'http://localhost:5173'), '/').'/pago/completado';

        $payload = [
            'appkey'               => config('services.libelula.appkey'),
            'identificador_deuda'  => $identificador,
            'email_cliente'        => $customerEmail,
            'emite_factura'        => true,
            'moneda'               => 'BOB', 
            'descuento_global'     => $descuentoGlobalMonto, 
            'valor_envio'          => 0, 
            'lineas_detalle_deuda' => $lineas, 
            'lineas_metadatos'     => [
                ['nombre'=>'order_id','dato'=>(string)$order->id],
                ['nombre'=>'user_id','dato'=>(string)$order->user_id],
            ],
            'callback_url'         => $callbackUrl,
            'url_retorno'          => $urlRetorno,
        ];

        try {
            // --- INICIO: MODIFICACIÓN (Punto 4) ---
            // Usamos el nuevo cliente HTTP con reintentos
            $resp = $this->libelula()->post(
                config('services.libelula.api_url').'/rest/deuda/registrar', 
                $payload
            );
            // --- FIN: MODIFICACIÓN ---

            $body = $resp->json();

            if ($resp->successful() && ($body['error'] ?? true) === false) {

                // --- INICIO: NUEVA LÓGICA (Punto 2c) ---
                // Guardamos los datos de auditoría
                $transaction->update([
                    'libelula_trans_id' => $body['id_transaccion'] ?? null,
                    'pasarela_url'      => $body['url_pasarela_pagos'] ?? null,
                    'payload_snapshot'  => $payload,
                    'amount_sent'       => $order->total_amount,
                    'currency'          => 'BOB',
                ]);
                // --- FIN: NUEVA LÓGICA ---

                return response()->json([
                    'url_pasarela_pagos' => $body['url_pasarela_pagos'],
                    'transaction_id'     => $body['id_transaccion'] ?? null,
                    'qr_simple_url'      => $body['qr_simple_url'] ?? null,
                ]);
            }

            $transaction->update(['status' => 'FALLIDO']);
            Log::error('Libélula registrar error', ['body'=>$body, 'http'=>$resp->status()]);
            return response()->json([
                'message' => $body['mensaje'] ?? 'Error al registrar la deuda',
            ], 422);

        } catch (\Throwable $e) {
            $transaction->update(['status' => 'FALLIDO']);
            Log::error('Excepción Libélula registrar', ['e'=>$e->getMessage()]);
            return response()->json(['message'=>'No se pudo conectar con el servicio de pagos.'], 503);
        }
    }

    /**
     * Webhook de pago exitoso (callback_url): acepta GET/POST.
     * Reconfirma contra Libélula antes de marcar pagado.
     */
    public function handlePagoExitoso(Request $req)
    {
        $transactionId = $req->query('transaction_id', $req->input('transaction_id'));
        $invoiceUrl    = $req->query('invoice_url', $req->input('invoice_url'));

        Log::info('Webhook Libélula recibido', ['payload'=>$req->all()]);

        if (!$transactionId) {
            return response()->json(['error'=>'transaction_id faltante'], 400);
        }

        $txn = LibelulaTransaction::where('libelula_trans_id', $transactionId)->first();
        if (!$txn) {
            Log::warning('Webhook: transacción no encontrada', ['transaction_id'=>$transactionId]);
            return response()->json(['error'=>'Transacción no encontrada'], 404);
        }

        if ($txn->status === 'PAGADO') {
            return response()->json(['status'=>'ok (idempotente)']);
        }

        try {
            // --- INICIO: MODIFICACIÓN (Punto 4) ---
            // Usamos el cliente HTTP con reintentos para la verificación
            $verify = $this->libelula()->post(
                config('services.libelula.api_url').'/rest/deuda/consultar_deudas/por_identificador', 
                [
                    'appkey' => config('services.libelula.appkey'),
                    'identificador' => $txn->identificador_deuda,
                ]
            )->json();
            // --- FIN: MODIFICACIÓN ---

            $pagado = (bool)(data_get($verify, 'datos.pagado') ?? data_get($verify,'pagado'));
        } catch (\Throwable $e) {
            Log::error('Fallo verificación Libélula', ['e'=>$e->getMessage()]);
            return response()->json(['error'=>'No se pudo verificar el pago'], 502);
        }

        if (!$pagado) {
            return response()->json(['status'=>'recibido (pendiente)']);
        }

        $txn->update([
            'status' => 'PAGADO',
            'factura_url' => $invoiceUrl,
        ]);

        $order = Order::find($txn->order_id);
        if ($order) {
            $order->update(['state' => 'paid']);
            event(new OrderPaid($order)); // <-- DISPARAR EL EVENTO
        }

        // TODO: dispara eventos/Jobs: mail, stock, etc.
        // event(new OrderPaid($order));

        return response()->json(['status'=>'ok']);
    }

    /**
     * Conciliación por rango (para CRON manual)
     */
    public function conciliar(Request $req)
    {
        $desde = $req->query('desde') ?? now()->subDays(2)->format('Y-m-d 00:00:00');
        $hasta = $req->query('hasta') ?? now()->format('Y-m-d 23:59:59');

        try {
            // --- INICIO: MODIFICACIÓN (Punto 4) ---
            // Usamos el cliente HTTP con reintentos para la conciliación
            $res = $this->libelula()->post(
                config('services.libelula.api_url').'/rest/deuda/consultar_pagos', 
                [
                    'appkey' => config('services.libelula.appkey'),
                    'fecha_inicial' => $desde,
                    'fecha_final'   => $hasta,
                ]
            )->json();
            // --- FIN: MODIFICACIÓN ---

            $pagos = data_get($res, 'datos', []);
            $count = 0;

            foreach ($pagos as $p) {
                $txn = LibelulaTransaction::where('identificador_deuda', $p['identificador'] ?? null)->first();

                if ($txn && $txn->status !== 'PAGADO') {
                    $txn->update([
                        'status' => 'PAGADO',
                        'factura_url' => data_get($p, 'facturas.0.url', $txn->factura_url),
                    ]);

                    $order = Order::find($txn->order_id);
                    if ($order && $order->state !== 'paid') {
                        $order->update(['state' => 'paid']);
                        event(new OrderPaid($order)); // <-- DISPARAR EL EVENTO
                    }
                    $count++;
                }
            }

            return response()->json(['ok'=>true, 'sincronizados'=>$count]);
        } catch (\Throwable $e) {
            Log::error('Conciliación Libélula error', ['e'=>$e->getMessage()]);
            return response()->json(['ok'=>false, 'error'=>'Fallo al conciliar'], 502);
        }
    }
}