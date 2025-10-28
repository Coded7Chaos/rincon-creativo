<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\Role;
use Illuminate\Database\Seeder;
//use Illuminate\Support\Facades\Hash;
use App\Enums\Departamento;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        //Admin de ejemplo
        User::factory()->create([
          'first_name' => 'Admin',
          'f_last_name' => 'Principal',
          's_last_name' => 'Sistema',
          'email' => 'admin@example.com',
          'password' => 'password123',
          'phone' => '60000001',
          'departamento' => Departamento::LaPaz,
          'city' => 'La Paz',
          'address' => 'Oficina Central, Av. Arce 2233',
          'role' => Role::Admin,
        ]);


        //Fulfillment de ejemplo
        User::factory()->create([
          'first_name' => 'Fulfillment',
          'f_last_name' => 'Usuario',
          's_last_name' => 'Almacen',
          'email' => 'fulfillment@example.com',
          'password' => 'password123',
          'phone' => '60000002',
          'departamento' => Departamento::SantaCruz,
          'city' => 'Santa Cruz',
          'address' => 'Almacén Central, Parque Industrial',
          'role' => Role::Fulfillment,
        ]);

        User::factory()->count(3)->create([
            'role' => Role::Client,
            'departamento' => Departamento::Cochabamba,
            'is_active' => true,
        ]);
    }
}
