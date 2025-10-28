<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\BinanceService;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use function Ratchet\Client\connect;

class BinanceStreamCommand extends Command
{
    /**
     * Nombre del comando
     */
    protected $signature = 'binance:stream';

    /**
     * Descripción
     */
    protected $description = 'Conecta al User Data Stream de Binance y escucha eventos en tiempo real. Ademas, reconecta automaticamente si la conexion se pierde';

    protected BinanceService $binanceService;
    protected LoopInterface $loop;
    protected int $reconnectDelay = 1;

    public function __construct(BinanceService $binanceService)
    {
        parent::__construct();

        $this->binanceService = $binanceService;
    }

    public function handle()
    {
        $this->loop = Factory::create();
        $this->connectToBinance();

        // Mantener el proceso corriendo indefinidamente
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

        // Timer para mantener viva la listenKey cada 30 minutos
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

                    if (isset($data['e'])) {
                        $event = $data['e'];
                        Log::info("Evento recibido: {$event}", $data);

                        if ($event === 'balanceUpdate') {
                            Log::info('Balance actualizado', $data);
                        }
                    } else {
                        Log::debug('Mensaje recibido sin campo "e"', $data ?? []);
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
    protected function scheduleReconnect(int $delay = null)
    {
        $delay = $delay ?? min($this->reconnectDelay, 60); // límite de 60s
        Log::info("Reintentando conexión en {$delay} segundos...");

        $this->loop->addTimer($delay, function () {
            $this->reconnectDelay = min($this->reconnectDelay * 2, 60);
            $this->connectToBinance();
        });
    }
}
