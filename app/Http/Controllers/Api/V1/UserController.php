<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Http\Requests\UserRequest;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

   
    /**
     * Display the specified resource.
     */
    public function show(User $user, Request $request)
    {
        if ($user->id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user)
    {
        if ($user->id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user->update($request->validated());

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user, Request $request)
    {
        if ($user->id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $authUser = $request->user();
        $authUser->tokens()->delete();
        $authUser->delete();

        return response()->noContent();
    }
}
