<?php

namespace App\Http\Controllers\Auth;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;


#[Group('Authentication', 'Authentication and session management', 1)]
class LoginController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    #[Endpoint(title: 'Login', description: 'Authenticate a user and return an access token.')]
    public function store(LoginRequest $request)//or return type array
    {
        $request->authenticate();

        $user = $request->user();
        
       $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => new UserResource($user),
            'token' => $token
            ];
    }

    /**
     * Destroy an authenticated session.
     */
    #[Endpoint(title: 'Logout', description: 'Revoke the current authenticated access token.')]
    public function destroy(Request $request): Response
    {
        // Auth::guard('web')->logout();

        // $request->session()->invalidate();

        // $request->session()->regenerateToken();
        $user= $request->user();
        $user->currentAccessToken()->delete();
        // $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    }
}
