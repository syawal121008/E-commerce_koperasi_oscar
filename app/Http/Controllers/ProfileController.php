<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();
            $hasChanges = false;

            // Update nama lengkap hanya jika diisi dan berbeda
            if ($request->filled('full_name') && trim($request->input('full_name')) !== '') {
                $newFullName = trim($request->input('full_name'));
                if ($newFullName !== $user->full_name) {
                    $user->full_name = $newFullName;
                    $hasChanges = true;
                    Log::info('Full name updated for user: ' . $user->user_id);
                }
            }
            
            // Update email hanya jika diisi dan berbeda
            if ($request->filled('email') && trim($request->input('email')) !== '') {
                $newEmail = trim($request->input('email'));
                if ($newEmail !== $user->email) {
                    $user->email = $newEmail;
                    $user->email_verified_at = null; // Reset verifikasi email
                    $hasChanges = true;
                    Log::info('Email updated for user: ' . $user->user_id);
                }
            }

            // Handle upload foto profil
            if ($request->hasFile('profile_photo')) {
                $file = $request->file('profile_photo');
                
                // Validasi file
                if (!$file->isValid()) {
                    Log::error('Invalid profile photo file for user: ' . $user->user_id);
                    return Redirect::route('profile.edit')->withErrors(['profile_photo' => 'File foto tidak valid.']);
                }

                // Pastikan folder profile_photos ada
                $profilePhotosPath = 'profile_photos';
                if (!Storage::disk('public')->exists($profilePhotosPath)) {
                    Storage::disk('public')->makeDirectory($profilePhotosPath, 0755, true);
                    Log::info('Created profile_photos directory');
                }

                // Hapus foto lama jika ada
                if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                    Storage::disk('public')->delete($user->profile_photo);
                    Log::info('Deleted old profile photo: ' . $user->profile_photo);
                }

                // Generate nama file unik
                $fileName = time() . '_' . $user->user_id . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                // Simpan foto baru ke storage/app/public/profile_photos
                $path = $file->storeAs($profilePhotosPath, $fileName, 'public');
                
                if ($path) {
                    $user->profile_photo = $path;
                    Log::info('New profile photo saved for user ' . $user->user_id . ' at path: ' . $path);
                    $hasChanges = true;
                } else {
                    Log::error('Failed to save profile photo for user: ' . $user->user_id);
                    return Redirect::route('profile.edit')->withErrors(['profile_photo' => 'Gagal menyimpan foto profil.']);
                }
            }

            // Simpan perubahan hanya jika ada yang berubah
            if ($hasChanges) {
                $saved = $user->save();
                if ($saved) {
                    Log::info('Profile updated paidfully for user: ' . $user->user_id);
                    return Redirect::route('profile.edit')->with('status', 'Profil berhasil diperbarui.');
                } else {
                    Log::error('Failed to save profile changes for user: ' . $user->user_id);
                    return Redirect::route('profile.edit')->withErrors(['error' => 'Gagal menyimpan perubahan profil.']);
                }
            } else {
                return Redirect::route('profile.edit')->with('info', 'Tidak ada perubahan yang disimpan karena tidak ada field yang diisi atau diubah.');
            }

        } catch (\Exception $e) {
            Log::error('Error updating profile for user ' . ($request->user()->user_id ?? 'unknown') . ': ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return Redirect::route('profile.edit')->withErrors(['error' => 'Terjadi kesalahan saat memperbarui profil.']);
        }
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

        // Hapus foto profil jika ada
        if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
            Storage::disk('public')->delete($user->profile_photo);
            Log::info('Deleted profile photo on account deletion: ' . $user->profile_photo);
        }

        // Hapus QR code jika ada
        if ($user->qr_code && str_contains($user->qr_code, 'qrcodes/') && Storage::disk('public')->exists($user->qr_code)) {
            Storage::disk('public')->delete($user->qr_code);
            Log::info('Deleted QR code on account deletion: ' . $user->qr_code);
        }

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}