<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function show(): View
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        // Laravel's native PasswordBroker: secure random token, hashed at
        // rest in password_reset_tokens, with a real expiry — replacing the
        // legacy weak rand()-based token stored in plaintext on staff.token.
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'The instructions to reset your password has been sent to your email. Please check your email.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
