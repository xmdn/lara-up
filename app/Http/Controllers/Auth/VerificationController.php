<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class VerificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('throttle:5,1')->only('verify', 'resend');
    }

    /**
     * Mark the user's email address as verified.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request, User $user)
    {
        if (! URL::hasValidSignature($request)) {
            return response()->json([
                'status' => trans('verification.invalid'),
            ], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => trans('verification.already_verified'),
            ], 400);
        }

        $user->markEmailAsVerified();

        event(new Verified($user));

        // I would prefer for verified user return token, token_type and expires_in.
        // As we do in LoginController, but leave this as it is (bc typing same login and pass two times is not good user experience).
        // And do as done here in the case when an important part of the uniqueness of the account, for example, the phone number is used for registration.

        return response()->json([
            'status' => trans('verification.verified'),
        ]);
    }

    /**
     * Resend the email verification notification.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resend(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (is_null($user)) {
            throw ValidationException::withMessages([
                'email' => [trans('verification.user')],
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => [trans('verification.already_verified')],
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['status' => trans('verification.sent')]);
    }
}
