<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\MasterPegawai;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $pegawai = MasterPegawai::with(['jabatan', 'bidang', 'atasanLangsung', 'ttdDigital'])
            ->findOrFail($request->user()->id);

        return view('profile.edit', [
            'pegawai' => $pegawai,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $pegawai = MasterPegawai::findOrFail($request->user()->id);

        // Update profile information
        $data = $request->validated();

        // Remove foto_profile from data as it's handled separately
        unset($data['foto_profile']);

        // Handle foto profile upload
        if ($request->hasFile('foto_profile')) {
            // Delete old photo if exists
            if ($pegawai->foto_profile_path && Storage::disk('public')->exists($pegawai->foto_profile_path)) {
                Storage::disk('public')->delete($pegawai->foto_profile_path);
            }

            // Store new photo
            $file = $request->file('foto_profile');
            $filename = 'profile_' . $pegawai->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/pegawai/foto', $filename, 'public');
            $data['foto_profile_path'] = $path;
        }

        $pegawai->update($data);

        // Update password if provided
        if ($request->filled('password')) {
            $request->validateWithBag('updatePassword', [
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'confirmed', 'min:8'],
            ], [
                'current_password.required' => 'Password saat ini wajib diisi.',
                'current_password.current_password' => 'Password saat ini tidak sesuai.',
                'password.required' => 'Password baru wajib diisi.',
                'password.confirmed' => 'Konfirmasi password tidak sesuai.',
                'password.min' => 'Password minimal 8 karakter.',
            ]);

            $pegawai->update([
                'password' => Hash::make($request->password),
            ]);

            return Redirect::route('profile.edit')->with('status', 'password-updated');
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
