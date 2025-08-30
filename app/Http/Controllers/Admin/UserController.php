<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        // Mengambil input dari filter
        $search = $request->query('search');
        $role = $request->query('role');

        // Memulai query builder
        $query = User::with(['products', 'orders', 'guruOrders', 'transactions', 'topups']);

        // Menerapkan filter pencarian nama jika ada
        if ($search) {
            $query->where('full_name', 'like', '%' . $search . '%');
        }

        // Menerapkan filter peran jika ada
        if ($role) {
            $query->where('role', 'like', $role);
        }
        
        // Mengurutkan dari yang terbaru (latest) dan melakukan paginasi (10 per halaman)
        $users = $query->latest()->paginate(10);

        // Mengembalikan view dengan data users dan input filter untuk ditampilkan kembali
        return view('admin.users.index', compact('users', 'search', 'role'));
    }


    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'student_id' => ['required', 'string', 'max:50', 'unique:users,student_id'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:' . User::ROLE_CUSTOMER . ',' . User::ROLE_GURU . ',' . User::ROLE_ADMIN],
            'balance' => ['required', 'numeric', 'min:0'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'full_name' => $request->full_name,
            'student_id' => $request->student_id,
            'email' => $request->email,
            'role' => $request->role,
            'balance' => $request->balance,
            'password' => Hash::make($request->password),
        ]);

        // Generate QR code
        try {
            $user->generateAndSaveQrCode();
        } catch (\Exception $e) {
            \Log::warning('Failed to generate QR code for new user: ' . $e->getMessage());
        }

        return redirect()->route('admin.users.index')->with('paid', 'User created paidfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        $user = User::where('user_id', $id)->firstOrFail();
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        // Cari user berdasarkan UUID
        $user = User::where('user_id', $id)->firstOrFail();

        // Validasi input - PERBAIKAN: menggunakan user_id sebagai primary key untuk unique rule
        $validated = $request->validate([
            'full_name' => 'nullable|string|max:255',
            'student_id' => 'nullable|string|max:50|unique:users,student_id,' . $user->user_id . ',user_id',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->user_id . ',user_id',
            'role' => 'nullable|in:' . implode(',', [User::ROLE_CUSTOMER, User::ROLE_GURU, User::ROLE_ADMIN]),
            'balance' => 'nullable|numeric|min:0',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Update data user
        $user->full_name = $validated['full_name'];
        $user->student_id = $validated['student_id'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->balance = $validated['balance'];

        // Update password jika diisi
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('paid', 'User updated paidfully.');
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $user = User::where('user_id', $id)
                    ->with(['products', 'orders', 'guruOrders', 'transactions', 'topups', 'reviews'])
                    ->firstOrFail();

        return view('admin.users.show', compact('user'));
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        $user = User::where('user_id', $id)->firstOrFail();

        if ($user->hasProfilePhoto()) {
            $user->deleteProfilePhoto();
        }

        if ($user->qr_code && str_contains($user->qr_code, 'qrcodes/')) {
            \Storage::disk('public')->delete($user->qr_code);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('paid', 'User deleted paidfully.');
    }
}