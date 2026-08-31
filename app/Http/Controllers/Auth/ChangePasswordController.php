<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Interfaces\BreadcrumbInterfaces;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ChangePasswordController extends Controller implements BreadcrumbInterfaces
{
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'Settings', 'route' => '', 'active' => false],
            ['name' => 'Change Password', 'route' => '', 'active' => true],
        ];
    }

    public function show(): View
    {
        return view('auth.change-password');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $staff = Auth::user();

        if (! Hash::check($request->input('current_password'), $staff->password)) {
            return back()->withErrors(['current_password' => 'Your current password is incorrect.']);
        }

        $staff->forceFill(['password' => Hash::make($request->input('password'))])->save();

        return back()->with('status', 'Your password has been changed successfully.');
    }
}
