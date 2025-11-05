<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\LibelulaTransaction;
use Illuminate\Http\Client\PendingRequest;

class ConciliarLibelula implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Crea una instancia del cliente HTTP con reintentos y timeout.
     * (Copiado del Punto 4)
     */
    private function libelula(): PendingRequest
    {
        return Http::timeout(20)      // Timeout de 20 segundos
                   ->retry(3, 200); // 3 reintentos, 200ms de espera
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('[JOB] Iniciando conciliación de Libélula...');

        // Definimos el rango de fechas (ej. últimas 48 horas)
        // El Job no recibe un Request, así que lo definimos aquí.
        $desde = now()->subDays(2)->format('Y-m-d 00:00:00');
        $hasta = now()->format('Y-m-d 23:59:59');

        try {
            $res = $this->libelula()->post(
                config('services.libelula.api_url').'/rest/deuda/consultar_pagos', 
                [
                    'appkey' => config('services.libelula.appkey'),
                    'fecha_inicial' => $desde,
                    'fecha_final'   => $hasta,
                ]
            )->json();

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
                    }
                    $count++;
                }
            }

            Log::info("[JOB] Conciliación de Libélula completada. Sincronizados: $count");

        } catch (\Throwable $e) {
            Log::error('[JOB] Conciliación Libélula error', ['e'=>$e->getMessage()]);
            // (Opcional) Podemos reintentar el job si falló la conexión
            // $this->release(300); // Reintentar en 5 minutos
        }
    }
}