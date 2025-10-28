<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\Role;
use App\Enums\Departamento;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin de ejemplo
        User::factory()->create([
            'first_name'    => 'Admin',
            'f_last_name'   => 'Principal',
            's_last_name'   => 'Sistema',
            'email'         => 'admin@example.com',
            'password'      => Hash::make('password123'),
            'phone'         => '60000001',
            'departamento'  => Departamento::LaPaz,
            'city'          => 'La Paz',
            'address'       => 'Oficina Central, Av. Arce 2233',
            'role'          => Role::Admin,
        ]);

        // Usuario Fulfillment
        User::factory()->create([
            'first_name'    => 'Fulfillment',
            'f_last_name'   => 'Usuario',
            's_last_name'   => 'Almacen',
            'email'         => 'fulfillment@example.com',
            'password'      => Hash::make('password123'),
            'phone'         => '60000002',
            'departamento'  => Departamento::SantaCruz,
            'city'          => 'Santa Cruz',
            'address'       => 'Almacén Central, Parque Industrial',
            'role'          => Role::Fulfillment,
        ]);

        // Clientes de prueba
        User::factory(3)->create([
            'role'          => Role::Client,
            'departamento'  => Departamento::Cochabamba,
        ]);
    }
}