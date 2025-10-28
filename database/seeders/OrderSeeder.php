<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;
use App\Enums\OrderState;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Seed the orders table.
     */
    public function run(): void
    {
        // Obtenemos algunos usuarios existentes para asignarles órdenes
        // Idea: tomamos todos los usuarios con rol Client
        $clients = User::where('role', \App\Enums\Role::Client)->get();

        // Si no hay clientes, igual asignamos al primer user para no romper
        if ($clients->isEmpty()) {
            $clients = User::all();
        }

        // Vamos a crear manualmente algunas órdenes por cada cliente
        foreach ($clients as $client) {

            // Orden 1
            $order1 = Order::create([
                'user_id'         => $client->id,
                'total_amount'    => 120.50,
                'state'           => OrderState::Delivered, 
                'global_discount' => 10, // 10%
                'created_at'      => Carbon::now()->subDays(5),
                'updated_at'      => Carbon::now()->subDays(5),
            ]);

            // Orden 2
            $order2 = Order::create([
                'user_id'         => $client->id,
                'total_amount'    => 75.00,
                'state'           => OrderState::Pending, // por ejemplo
                'global_discount' => 0,
                'created_at'      => Carbon::now()->subDays(2),
                'updated_at'      => Carbon::now()->subDays(2),
            ]);

            // Guardamos ids para que OrderDetailSeeder pueda usarlos si quisieras hacer lógica cruzada.
            // En este caso no necesitamos pasar nada global, así que lo dejamos aquí.
        }
    }
}