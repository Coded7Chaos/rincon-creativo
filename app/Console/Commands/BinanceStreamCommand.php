<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Services\BinanceService;
use App\Services\OrderService;

use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use function Ratchet\Client\connect;

class BinanceStreamCommand extends Command
{
    protected $signature = 'binance:stream';

    protected $description = 'Conecta al User Data Stream de Binance y escucha eventos en tiempo real. Ademas, reconecta automaticamente si la conexion se pierde';

    protected BinanceService $binanceService;
    protected OrderService $orderService;
    protected LoopInterface $loop;
    protected int $reconnectDelay = 1;

    public function __construct(
        BinanceService $binanceService,
        OrderService $orderService
    ) {
        parent::__construct();

        $this->binanceService = $binanceService;
        $this->orderService   = $orderService;
    }

    public function handle()
    {
        $this->loop = Factory::create();
        $this->connectToBinance();
        $this->loop->run();
    }

    protected function connectToBinance()
    {
        $listenKey = $this->binanceService->getCachedListenKey();

        if (!$listenKey) {
            $this->warn('No hay listenKey en caché. Creando una nueva...');
            $listenKey = $this->binanceService->crearListenKey();

            if (!$listenKey) {
                Log::critical('No se pudo obtener listenKey. Reintentando en 20 segundos.');
                $this->scheduleReconnect(20);
                return;
            }
        }

        $wsUrl = "wss://stream.binance.com:9443/ws/{$listenKey}";
        $this->info("Conectando a WebSocket: {$wsUrl}");
        Log::info("Conectando a WebSocket Binance con listenKey {$listenKey}");

        // Renueva listenKey periódicamente
        $this->loop->addPeriodicTimer(30 * 60, function () {
            $ok = $this->binanceService->keepAlive();
            if (!$ok) {
                Log::warning('Falló la renovación de listenKey, creando una nueva...');
                $this->binanceService->crearListenKey();
            } else {
                Log::info('ListenKey renovada correctamente.');
            }
        });

        connect($wsUrl, [], [], $this->loop)->then(
            function ($conn) {
                $this->info('Conectado al stream.');
                $this->reconnectDelay = 1;

                $conn->on('message', function ($msg) {
                    $data = json_decode($msg, true);

                    if (!is_array($data)) {
                        Log::debug('Mensaje no JSON decodificable');
                        return;
                    }

                    if (!isset($data['e'])) {
                        Log::debug('Mensaje sin campo "e"', $data);
                        return;
                    }

                    $eventType = $data['e'];
                    Log::info("Evento recibido: {$eventType}", $data);

                    if ($eventType === 'balanceUpdate') {
                        $this->handleBalanceUpdate($data);
                    }
                });

                $conn->on('close', function ($code = null, $reason = null) {
                    Log::warning("Conexión cerrada ({$code}): {$reason}");
                    $this->scheduleReconnect();
                });
            },
            function ($e) {
                Log::error("Error conectando al WebSocket: " . $e->getMessage());
                $this->scheduleReconnect();
            }
        );
    }

    protected function handleBalanceUpdate(array $payload): void
    {
        // Ejemplo payload balanceUpdate esperado de Binance Spot:
        // {
        //   "e": "balanceUpdate",
        //   "E": 1730123123123,
        //   "a": "USDT",
        //   "d": "15.00000000",
        //   "T": 1730123123000
        // }

        $asset = $payload['a'] ?? null;
        $deltaStr = $payload['d'] ?? null;
        $timestampMs = $payload['T'] ?? null;

        if ($asset === null || $deltaStr === null || $timestampMs === null) {
            Log::warning('[BinanceWS] balanceUpdate incompleto', $payload);
            return;
        }

        $delta = (float)$deltaStr;

        // sólo nos interesan depósitos (d > 0)
        if ($delta <= 0) {
            Log::info("[BinanceWS] balanceUpdate ignorado porque d <= 0 ({$delta})");
            return;
        }

        Log::info("[BinanceWS] Deposito detectado: +{$delta} {$asset}");

        // Intentar casar con una orden UNPAID
        $matchedOrder = $this->orderService->matchIncomingDeposit(
            $delta,
            $asset,
            (int)$timestampMs
        );

        if ($matchedOrder) {
            Log::info("[BinanceWS] Orden {$matchedOrder->id} marcada como PAGADA automáticamente.");
        } else {
            Log::info("[BinanceWS] No se pudo casar el pago con ninguna orden pendiente.");
        }
    }

    protected function scheduleReconnect(int $delay = null)
    {
        $delay = $delay ?? min($this->reconnectDelay, 60);
        Log::info("Reintentando conexión en {$delay} segundos...");

        $this->loop->addTimer($delay, function () {
            $this->reconnectDelay = min($this->reconnectDelay * 2, 60);
            $this->connectToBinance();
        });
    }
}