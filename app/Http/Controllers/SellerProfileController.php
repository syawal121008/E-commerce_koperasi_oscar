<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SellerProfile;
use App\Models\User;
use App\Models\Role;


class SellerProfileController extends Controller
{
    /**
     * Display a listing of seller profiles (ADMIN ONLY)
     */
    public function index()
    {
        $user = auth()->user();
        
        // Hanya admin yang bisa lihat semua seller profiles
        if (!$user->isAdmin()) {
            abort(403, 'Hanya admin yang dapat melihat semua profil penjual.');
        }
        
        $profiles = SellerProfile::with('user')->paginate(10);
        return view('seller_profiles.index', compact('profiles'));
    }

    /**
     * Show the form for creating a new seller profile
     */
    public function create()
    {
        $user = auth()->user();
        
        // Cek apakah user sudah punya seller profile
        if ($user->sellerProfile) {
            return redirect()->route('seller_profiles.show', $user->sellerProfile->seller_profile_id)
                   ->with('info', 'Anda sudah memiliki profil penjual. Anda hanya dapat membuat satu profil.');
        }
        
        return view('seller_profiles.create');
    }

    /**
     * Store a newly created seller profile
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Cek kalau user sudah punya seller profile
        if ($user->sellerProfile) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Anda sudah memiliki profil penjual.'], 400);
            }
            return back()->withErrors(['error' => 'Anda sudah memiliki profil penjual.']);
        }

        try {
            // Validasi input
            $validated = $request->validate([
                'store_name' => 'required|string|max:255|unique:seller_profile,store_name',
                'store_description' => 'nullable|string',
                'business_type' => 'nullable|string|max:255',
                'business_address' => 'nullable|string',
                'bank_account' => 'nullable|string|max:255',
                'bank_name' => 'nullable|string|max:255',
                'npwp' => 'nullable|string|max:255',
            ]);

            // Set user_id dari user yang login
            $validated['user_id'] = $user->user_id;
            $validated['verified'] = false; // Default belum verified

            // Simpan seller profile
            $sellerProfile = SellerProfile::create($validated);

            // Return JSON sukses untuk AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Profil penjual berhasil dibuat. Tunggu verifikasi admin.',
                    'seller_profile_id' => $sellerProfile->seller_profile_id
                ], 201);
            }

            return redirect()->route('seller_profiles.show', $sellerProfile->seller_profile_id)
                           ->with('success', 'Profil penjual berhasil dibuat.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Error creating seller profile: ' . $e->getMessage());
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'], 500);
            }
            return back()->withErrors(['error' => 'Terjadi kesalahan sistem.']);
        }
    }

    /**
     * Display the specified seller profile
     */
    public function show(SellerProfile $sellerProfile)
    {
        $user = auth()->user();
        
        // User hanya bisa lihat profil sendiri, admin bisa lihat semua
        if (!$user->isAdmin() && $sellerProfile->user_id !== $user->user_id) {
            abort(403, 'Anda hanya dapat melihat profil penjual Anda sendiri.');
        }
        
        $sellerProfile->load('user');
        return view('seller_profiles.show', compact('sellerProfile'));
    }

    /**
     * Show the form for editing the specified seller profile
     */
    public function edit(SellerProfile $sellerProfile)
    {
        $user = auth()->user();
        
        // User hanya bisa edit profil sendiri, admin bisa edit semua
        if (!$user->isAdmin() && $sellerProfile->user_id !== $user->user_id) {
            abort(403, 'Anda hanya dapat mengedit profil penjual Anda sendiri.');
        }
        
        return view('seller_profiles.edit', compact('sellerProfile'));
    }

    /**
     * Update the specified seller profile
     */
    public function update(Request $request, SellerProfile $sellerProfile)
    {
        $user = auth()->user();
        
        // User hanya bisa edit profil sendiri, admin bisa edit semua
        if (!$user->isAdmin() && $sellerProfile->user_id !== $user->user_id) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Anda hanya dapat mengedit profil penjual Anda sendiri.'], 403);
            }
            abort(403, 'Anda hanya dapat mengedit profil penjual Anda sendiri.');
        }
        
        try {
            $validated = $request->validate([
                'store_name' => 'required|string|max:255|unique:seller_profile,store_name,' . $sellerProfile->seller_profile_id . ',seller_profile_id',
                'store_description' => 'nullable|string',
                'business_type' => 'nullable|string|max:255',
                'business_address' => 'nullable|string',
                'bank_account' => 'nullable|string|max:255',
                'bank_name' => 'nullable|string|max:255',
                'npwp' => 'nullable|string|max:255',
            ]);

            // Hanya admin yang bisa mengubah status verified
            if ($user->isAdmin() && $request->has('verified')) {
                $validated['verified'] = $request->boolean('verified');
            }

            $sellerProfile->update($validated);

            // Jika request JSON (dari modal), return JSON response
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Profil penjual berhasil diperbarui.',
                    'seller_profile' => $sellerProfile
                ], 200);
            }

            return redirect()->route('seller_profiles.show', $sellerProfile->seller_profile_id)
                            ->with('success', 'Profil penjual berhasil diperbarui.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Error updating seller profile: ' . $e->getMessage());
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'], 500);
            }
            return back()->withErrors(['error' => 'Terjadi kesalahan sistem.']);
        }
    }

    /**
     * Remove the specified seller profile (ADMIN ONLY)
     */
    public function destroy(SellerProfile $sellerProfile)
    {
        $user = auth()->user();
        
        // Hanya admin yang bisa hapus seller profile
        if (!$user->isAdmin()) {
            abort(403, 'Hanya admin yang dapat menghapus profil penjual.');
        }
        
        // Cek apakah seller profile punya products
        if ($sellerProfile->products()->count() > 0) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus profil yang masih memiliki produk.']);
        }
        
        $sellerProfile->delete();
        
        return redirect()->route('seller_profiles.index')
                        ->with('success', 'Profil penjual berhasil dihapus.');
    }

    /**
     * My Profile - User melihat profil sendiri
     */
    public function myProfile()
    {
        $user = auth()->user();
        $sellerProfile = $user->sellerProfile;
        
        if (!$sellerProfile) {
            return redirect()->route('seller_profiles.create')
                           ->with('info', 'Anda belum memiliki profil penjual. Silakan buat terlebih dahulu.');
        }
        
        return view('seller_profiles.show', compact('sellerProfile'));
    }

    /**
     * Admin verify seller profile
     */
    public function verify(SellerProfile $sellerProfile)
    {
        $user = auth()->user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Hanya admin yang dapat memverifikasi profil penjual.');
        }
        
        $sellerProfile->update(['verified' => true]);
        
        // Update role user menjadi seller
        $sellerRole = Role::where('role_name', 'guru')->first();

        if ($sellerRole) {
            $profileOwner = $sellerProfile->user; 

            if ($profileOwner && $profileOwner->role_id != $sellerRole->role_id) {
                $profileOwner->role_id = $sellerRole->role_id;
                $profileOwner->save();
            }
        } else {
            return back()->withErrors(['error' => 'Gagal mengubah role user. Role "seller" tidak ditemukan.']);
        }
        
        return back()->with('success', 'Profil penjual berhasil diverifikasi dan role user telah diupdate.');
    }

    /**
     * Admin unverify seller profile
     */
    public function unverify(SellerProfile $sellerProfile)
    {
        $user = auth()->user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Hanya admin yang dapat membatalkan verifikasi profil penjual.');
        }
        
        $sellerProfile->update(['verified' => false]);
        
        return back()->with('success', 'Verifikasi profil penjual berhasil dibatalkan.');
    }
}