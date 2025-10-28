<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderDetail;

class OrderDetailSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::all();

        foreach ($orders as $order) {

            OrderDetail::create([
                'order_id'        => $order->id,
                'product_id'      => 1,
                'quantity'        => 2,
                'subtotal_price'  => 80.00,
                'unit_discount'   => 5,
            ]);

            OrderDetail::create([
                'order_id'        => $order->id,
                'product_id'      => 2,
                'quantity'        => 1,
                'subtotal_price'  => 38.50,
                'unit_discount'   => 0,
            ]);

            OrderDetail::create([
                'order_id'        => $order->id,
                'product_id'      => 3,
                'quantity'        => 5,
                'subtotal_price'  => 100.00,
                'unit_discount'   => 10,
            ]);
        }
    }
}