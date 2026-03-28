<?php

namespace App\Http\Controllers\Auth;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

#[Group('Email Verification', 'Email verification flows', 2)]
class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    #[Endpoint(title: 'Send Verification Email', description: 'Send a new email verification notification.')]
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended('/dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['status' => 'verification-link-sent']);
    }
}
