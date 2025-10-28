<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class BinanceService
{
    protected $apiKey;
    protected $apiSecret;
    protected $baseUrl;
    protected $client;

    public function __construct()
    {
        // Asegúrate de que las variables en .env estén con comillas
        $this->apiKey = env('BINANCE_API_KEY');
        $this->apiSecret = env('BINANCE_API_SECRET');
        $this->baseUrl = env('BINANCE_API_URL');

        if (empty($this->apiKey) || empty($this->baseUrl)) {
            Log::critical('BINANCE_API_KEY o BINANCE_API_URL no están definidas en el .env');
        }
    }

    /**
     * PROCESO 1: Crea listenKey y lo guarda en cache (TTL 60 minutos menos un margen)
     *
     * Endpoint: POST /api/v3/userDataStream
     * Seguridad: apiKey
     *
     * @return string|null La listenKey si tiene éxito, o null si falla.
     *  
     */

    public function crearListenKey(): ?string
    {   

        $endpoint = '/api/v3/userDataStream';
        $urlCompleta = $this->baseUrl . $endpoint;

        // Log para depurar: ver la URL exacta que estamos llamando
        Log::debug('Intentando llamar a Binance URL: ' . $urlCompleta);

        try {
            $response = Http::withHeaders([
                'X-MBX-APIKEY' => $this->apiKey
            ])->withBody('', 'application/json')->post($urlCompleta);

            // Verificar si la petición fue exitosa (código 200)
            if ($response->successful()) {
                
                $data = $response->json();
                $listenKey = $data['listenKey'];
                if(!$listenKey){
                    throw new Exception('Respuesta invalida: listenkey no encontrada.');
                }
                Log::info("Nueva listenKey obtenida con éxito: {$listenKey}");
                
                Cache::put('binance_listenKey', $listenKey, now()->addMinutes(55));
                return $listenKey;

            } else {
                // La petición falló
                Log::error('Error al crear listenKey.', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

        } catch (\Exception $e) {
            // Error de conexión o algo peor
            Log::critical('Excepción al conectar con Binance API.', [
                'message' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    // y 'getDepositHistory' (Proceso 3, que SÍ usará la firma)
    public function keepAlive(?string $listenKey = null): bool
    {
        $listenKey = $listenKey ?: Cache::get('binance_listenKey');

        if (!$listenKey) {
            Log::warning('No hay listenKey en cache para renovar.');
            return false;
        }

        $response = Http::withHeaders([
            'X-MBX-APIKEY' => $this->apiKey,
        ])->put("{$this->baseUrl}/api/v3/userDataStream", [
            'listenKey' => $listenKey,
        ]);

        if ($response->failed()) {
            Log::error('Error al renovar listenKey', ['body' => $response->body()]);
            return false;
        }

        // Renovamos el TTL en cache
        Cache::put('binance_listenKey', $listenKey, now()->addMinutes(55));
        Log::info('ListenKey renovada correctamente.');
        return true;
    }

    /**
     * Cerrar (eliminar) una listenKey activa
     */
    public function closeListenKey(?string $listenKey = null): bool
    {
        $listenKey = $listenKey ?: Cache::pull('binance_listenKey');
        if (!$listenKey) return false;

        $response = Http::withHeaders([
            'X-MBX-APIKEY' => $this->apiKey,
        ])->delete("{$this->baseUrl}/api/v3/userDataStream", [
            'listenKey' => $listenKey,
        ]);

        if ($response->failed()) {
            Log::error('Error al cerrar listenKey', ['body' => $response->body()]);
            return false;
        }

        Log::info("🧹 ListenKey cerrada correctamente: {$listenKey}");
        return true;
    }

    /**
     * Obtener la listenKey almacenada en cache
     */
    public function getCachedListenKey(): ?string
    {
        return Cache::get('binance_listenKey');
    }
}