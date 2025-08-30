<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ViewModeController extends Controller
{
    /**
     * Mengalihkan mode tampilan antara customer dan admin.
     */
    public function switchMode()
    {
        // Pastikan user sudah login dan merupakan admin terverifikasi
        if (!Auth::check() || !Auth::user()->isVerifiedadmin()) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke fitur ini.');
        }

        // Dapatkan mode saat ini dari session, defaultnya 'customer'
        $currentMode = session('view_mode', 'customer');

        // Alihkan mode
        $newMode = ($currentMode === 'customer') ? 'admin' : 'customer';

        // Simpan mode baru ke session
        session(['view_mode' => $newMode]);

        // Redirect kembali ke dashboard dengan mode baru
        return redirect()->route('dashboard')->with('status', 'Tampilan berhasil diubah ke mode ' . ucfirst($newMode));
    }
}