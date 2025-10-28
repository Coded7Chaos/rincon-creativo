<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Enums\Departamento;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();

        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
            'role' => ['required', new Enum(Role::class)], 
        ]);

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
            'role' => $data['role'],
        ]);
        
        return response()->json([
            'message' => 'Usuario con rol creado exitosamente',
            'user' => $user
        ], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'Usuario eliminado correctamente.']);
    }
}
