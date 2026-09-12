<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AccessType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\UserLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors(['email' => 'Invalid login credentials. Please try to login again.'])
                ->onlyInput('email');
        }

        $staff = Auth::user();

        if (! $staff->isAdmin() || ! $staff->is_active) {
            Auth::logout();

            return back()->withErrors(['email' => 'Invalid login credentials. Please try to login again.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        UserLog::create([
            'ip_address' => $request->ip(),
            'access_date' => now(),
            'access_type' => AccessType::Login,
            'staff_id' => $staff->id,
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $staff = Auth::user();

        if ($staff) {
            UserLog::create([
                'ip_address' => $request->ip(),
                'access_date' => now(),
                'access_type' => AccessType::Logout,
                'staff_id' => $staff->id,
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
