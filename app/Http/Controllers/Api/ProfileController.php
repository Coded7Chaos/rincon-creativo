<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Resources\UserDataResource;
//use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Enums\Departamento;

class ProfileController extends Controller
{

    public function show(Request $request){
        $user = $request->user()->load([
            'orders.details.product',
        ]);

        return new UserDataResource($user);
    }
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        //
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // "sometimes" = si el campo viene en la request, lo valido; si no viene, lo ignoro.
        $validator = Validator::make($request->all(), [
            'first_name'   => ['sometimes', 'string', 'max:255'],
            'f_last_name'  => ['sometimes', 'string', 'max:255'],
            's_last_name'  => ['sometimes', 'string', 'max:255', 'nullable'],
            'phone'        => ['sometimes', 'string', 'max:20'],
            'departamento' => ['sometimes', Rule::enum(Departamento::class)],
            'city'         => ['sometimes', 'string', 'max:255'],
            'address'      => ['sometimes', 'string', 'max:255', 'nullable'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $user->fill($data);
        $user->save();
        $user->refresh();

        return response()->json([
            'message' => 'Perfil actualizado correctamente.',
            'user'    => new UserDataResource($user),
        ], 200);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        //
    }
}
