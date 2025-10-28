<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Enums\Role;
use App\Enums\Departamento;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define el estado por defecto del modelo User.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Lista de departamentos (puedes ajustarla a los valores de tu enum Departamento)
        $departamentos = [
            Departamento::LaPaz,
            Departamento::Cochabamba,
            Departamento::SantaCruz,
        ];

        // Lista de roles básicos
        $roles = [
            Role::Client,
            Role::Fulfillment,
            Role::Admin,
        ];

        return [
            'first_name'    => fake()->firstName(),
            'f_last_name'   => fake()->lastName(),
            's_last_name'   => fake()->lastName(),
            'email'         => fake()->unique()->safeEmail(),
            'password'      => Hash::make('password123'), // contraseña por defecto
            'phone'         => fake()->numerify('6#######'),
            'departamento'  => fake()->randomElement($departamentos),
            'city'          => fake()->city(),
            'address'       => fake()->streetAddress(),
            'role'          => fake()->randomElement($roles),
            'email_verified_at' => now(),
            'remember_token'    => Str::random(10),
        ];
    }

    /**
     * Estado para usuarios no verificados.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}