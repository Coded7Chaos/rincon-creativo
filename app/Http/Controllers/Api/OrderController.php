<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Enums\OrderState;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\OrderResource;


class OrderController extends Controller
{

    /**
     * MÉTODO 1: Iniciar el proceso de pago.
     * El frontend envía el carrito aquí.
     * Este método NO guarda en BD, solo habla con Binance.
     */
    public function initiatePayment(Request $request)
    {
        // 1. Validar el carrito
        $cartData = $request->validate([
            'products' => 'required|array',
            'products.*.product_id' => 'required|integer|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.discount' => 'nullable|integer|min:0|max:100', // El descuento que ofreces
        ]);

        $totalAmount = 0;
        $productsForBinance = []; // Datos para enviar a Binance

        // 2. Calcular el total REAL en el backend (NUNCA confíes en el frontend)
        foreach ($cartData['products'] as $item) {
            $product = Product::find($item['product_id']);
            $unit_discount = $item['discount'] ?? 0;
            
            // Calculamos el precio con descuento
            $priceWithDiscount = $product->price - ($product->price * $unit_discount / 100);
            
            // Calculamos el subtotal de este item
            $subtotal = $priceWithDiscount * $item['quantity'];
            $totalAmount += $subtotal;
        }
        // $binanceResponse = Http::post('https://api.binance.com/pay/...', [
        //     'total_amount' => $totalAmount,
        //     'currency' => 'USDT',
        //     'description' => 'Pago de Orden #' . uniqid(),
        //     'merchant_order_id' => 'MY_INTERNAL_ID_123', // Un ID tuyo
        //     'cart_data' => $cartData['products'] // (Opcional) Guardar el carrito
        // ]);
        // $paymentUrl = $binanceResponse->json()['payment_url'];

        // 4. Devuelves la URL de pago al frontend
        return response()->json([
            'message' => 'Solicitud de pago creada.',
            'payment_url' => 'https://url.de.binance.para.pagar.com/...' // URL real de Binance
        ]);
    }

    /**
     * MÉTODO 2: Webhook de Binance Pay
     * Binance llama a esta ruta DESPUÉS de que el usuario pague.
     * Aquí es donde SÍ guardamos en la base de datos.
     */
    public function handlePaymentSuccess(Request $request)
    {
        $userId = $request->input('user_id'); // ID del usuario
        $totalPagado = $request->input('total_paid'); // Total que Binance dice que pagó
        $order = null;
        try{
            DB::beginTransaction();
            //Creacion de la orden principal
            $order = Order::create([
                'user_id' => $userId,
                'total_amount' => $totalPagado,
                //'state' => OrderState::Pending,
                'global_discount' => 0,
            ]);
            foreach ($cartData['products'] as $item) {
                $product = Product::find($item['product_id']);
                $unit_discount = $item['discount'] ?? 0;
                $priceWithDiscount = $product->price - ($product->price * $unit_discount / 100);
                $subtotalPrice = $priceWithDiscount * $item['quantity'];
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'subtotal_price' => $subtotalPrice,
                    'unit_discount' => $unit_discount,
                ]);

                // (Aquí también deberías descontar el stock del producto)
                // $product->decrement('stock', $item['quantity']);
            }
            DB::commit();

            return response() -> json(['message' => 'Orden procesada exitosamente.'],200);

        } catch(\Exception $e){
            DB::rollBack();
            \Log::error('Error al procesar la orden: ' . $e->getMessage());
            return response() -> json(['message' => 'Error interno al procesar la orden.'], 500);
        }
    }

    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::with([
            'user',
            'details.product',
        ])->get();

        // Devolvemos una colección de OrderResource
        return OrderResource::collection($orders);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(order $order)
    {
        //
    }
}
