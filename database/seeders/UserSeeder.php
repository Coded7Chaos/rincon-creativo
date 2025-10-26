<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        User::FirstOrCreate(
          [
            'email' => 'admin@rinconcreativo.com'
          ],
          [
            'first_name' => 'Admin',
            'f_last_name' => '.',
            's_last_name' => '.',
            'password' => Hash::make('1234567890'),
            'role' => Role::Admin,
            'email_verified_at' => now(),
          ]
        );
    }
}
