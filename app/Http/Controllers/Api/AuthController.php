<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Enums\Departamento;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\Enum;


class AuthController extends Controller
{
    /**
     * Registro de nuevos usuarios
     */
    public function register(Request $request)
    {
        // 1. Validar los datos
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'f_last_name' => 'required|string|max:255',
            's_last_name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => ['required',
                        'string',
                        'digits:8',
                        'regex:/^[67]\d{7}$/'],
            'departamento' => ['required', new Enum(Departamento::class)],
            'city' => 'required|string|max:255',
            'address' => 'required|string|max:350|min:10',
        ]);

        try{
        // 2. Crear el usuario
        $user = User::create([
            'first_name' => $data['first_name'],
            'f_last_name' => $data['f_last_name'],
            's_last_name' => $data['s_last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'],
            'departamento' => $data['departamento'],
            'city' => $data['city'],
            'address' => $data['address'],
        ]);

        // 3. Crear el token
        $token = $user->createToken('auth_token')->plainTextToken;

        // 4. Devolver la respuesta JSON
        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201); // 201 = Created
        } catch(\Exception $e){
            Log::error('Error en el registro de usuario: ' . $e->getMessage());
            return response()->json([
            'message' => 'Ocurrió un error inesperado al registrar el usuario.'
        ], 500);
        }
    }

    /**
     * Login de usuarios
     */
    public function login(Request $request)
    {
        // 1. Validar
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Intentar autenticar
        if (!Auth::attempt($credentials)) {
            // Si las credenciales son incorrectas
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401); // 401 = Unauthorized
        }

        // 3. Si las credenciales son correctas, buscar al usuario
        $user = User::where('email', $request->email)->firstOrFail();

        // 4. Crear el token
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Devolver la respuesta JSON
        return response()->json([
            'message' => 'Login exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 200); // 200 = OK
    }

    /**
     * Logout de usuarios
     */
    public function logout(Request $request)
    {
        // Revoca el token de la petición actual
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente'
        ], 200);
    }
}