<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Primero aseguramos categorías básicas
        // (ajusta nombres o añade más si tu app ya tiene CategorySeeder)
        $categories = [
            ['id' => 1, 'nombre' => 'Ropa'],
            ['id' => 2, 'nombre' => 'Accesorios'],
            ['id' => 3, 'nombre' => 'Electrónica'],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->updateOrInsert(
                ['id' => $cat['id']],
                ['nombre' => $cat['nombre'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // Ahora insertamos productos
        $productos = [
            [
                'nombre'       => 'Camiseta Negra Unisex',
                'descripcion'  => 'Camiseta básica de algodón 100%, color negro.',
                'precio'       => 45.00,
                'stock'        => 50,
                'imagen_url'   => 'https://via.placeholder.com/300x300.png?text=Camiseta+Negra',
                'category_id'  => 1,
            ],
            [
                'nombre'       => 'Gorra Clásica Blanca',
                'descripcion'  => 'Gorra ajustable con logo bordado.',
                'precio'       => 38.50,
                'stock'        => 30,
                'imagen_url'   => 'https://via.placeholder.com/300x300.png?text=Gorra+Blanca',
                'category_id'  => 2,
            ],
            [
                'nombre'       => 'Sudadera con Capucha Gris',
                'descripcion'  => 'Sudadera cómoda y cálida, material de poliéster y algodón.',
                'precio'       => 95.99,
                'stock'        => 20,
                'imagen_url'   => 'https://via.placeholder.com/300x300.png?text=Sudadera+Gris',
                'category_id'  => 1,
            ],
            [
                'nombre'       => 'Audífonos Inalámbricos',
                'descripcion'  => 'Bluetooth 5.0, micrófono integrado, hasta 8h de batería.',
                'precio'       => 180.00,
                'stock'        => 15,
                'imagen_url'   => 'https://via.placeholder.com/300x300.png?text=Audifonos',
                'category_id'  => 3,
            ],
            [
                'nombre'       => 'Cargador Rápido USB-C 25W',
                'descripcion'  => 'Cargador compatible con smartphones y tablets modernos.',
                'precio'       => 70.00,
                'stock'        => 40,
                'imagen_url'   => 'https://via.placeholder.com/300x300.png?text=Cargador+USB-C',
                'category_id'  => 3,
            ],
        ];

        foreach ($productos as $p) {
            Product::updateOrCreate(
                ['nombre' => $p['nombre']],
                [
                    'descripcion'  => $p['descripcion'],
                    'precio'       => $p['precio'],
                    'stock'        => $p['stock'],
                    'imagen_url'   => $p['imagen_url'],
                    'category_id'  => $p['category_id'],
                    'created_at'   => Carbon::now(),
                    'updated_at'   => Carbon::now(),
                ]
            );
        }
    }
}