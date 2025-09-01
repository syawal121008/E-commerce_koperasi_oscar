<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    /**
     * Menangani permintaan registrasi, membuat pengguna, menghasilkan QR code,
     * lalu login dan mengarahkan pengguna berdasarkan role.
     */
    public function store(Request $request)
    {
        // Validasi sudah ditangani secara otomatis oleh RegisterUserRequest

        DB::beginTransaction();

        try {
            // 1. Buat user dari data yang sudah tervalidasi
            $user = User::create([
                'user_id'    => Str::uuid(),
                'full_name'  => $request->full_name,
                'student_id' => $request->student_id,
                'email'      => $request->email,
                'password'   => Hash::make($request->password),
                'role'       => $request->role,
                'balance'    => 0.00,
            ]);

            // 2. Generate dan simpan path QR code
            $qrPath = app(QrCodeService::class)->generateForUser($user);
            $user->qr_code = $qrPath;
            $user->save();

            DB::commit();

            // 3. Login pengguna yang baru dibuat
            Auth::login($user);

            // 4. Arahkan berdasarkan role dengan pesan sukses
            $redirectUrl = $this->getRedirectUrlByRole($user->role);
            
            return redirect($redirectUrl)->with('success', 'Registrasi berhasil! Selamat datang.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Kesalahan Registrasi: ' . $e->getMessage());

            // Kembalikan ke halaman sebelumnya dengan pesan error
            return back()->withInput()->with('error', 'Registrasi gagal. Silakan coba lagi.');
        }
    }

    /**
     * Menentukan URL redirect berdasarkan role user
     */
    private function getRedirectUrlByRole($role)
    {
        switch ($role) {
            case 'customer':
                return '/shop';
            case 'admin':
            case 'guru':
                return '/dashboard';
            default:
                return '/dashboard'; // Default fallback
        }
    }
}