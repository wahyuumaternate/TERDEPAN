<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordController extends Controller
{
    /**
     * Halaman wajib ganti password (docs/flow/02-alur-autentikasi.md §Login Awal - Mandiri) —
     * ditampilkan lewat redirect App\Http\Middleware\EnsurePasswordIsChanged, form-nya submit
     * ke update() di bawah (route yang sama dipakai halaman profile).
     */
    public function forceCreate(): View
    {
        return view('auth.force-password');
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $wasForced = $request->user()->must_change_password;

        $request->user()->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ]);

        if ($wasForced) {
            return redirect()->route('dashboard')->with('status', 'password-updated');
        }

        return back()->with('status', 'password-updated');
    }
}
