<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Resources\UserDataResource;

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
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        //
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        //
    }
}
