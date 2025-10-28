<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Services\BinanceService;

class TestController extends Controller
{
    protected $binanceService;

    public function __construct(BinanceService $binanceService)
    {
        $this->binanceService = $binanceService;
    }

    public function probarListenKey()
    {
        $key = $this->binanceService->crearListenKey();

        if ($key) {
            return response()->json(['listenKey' => $key]);
        } else {
            return response()->json(['error' => 'No se pudo obtener la key'], 500);
        }
    }
}
