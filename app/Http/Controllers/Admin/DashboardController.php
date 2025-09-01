<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Products;
use App\Models\Orders;
use App\Models\SellerProfile;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik Utama - MENGGUNAKAN RELASI ROLE
        $data = [
            'role' => 'admin',
            'total_users' => User::count(),
            'total_sellers' => User::whereHas('role', function($query) {
                $query->where('role_name', 'guru');
            })->count(),
            'total_customers' => User::whereHas('role', function($query) {
                $query->where('role_name', 'customer');
            })->count(),
            'total_products' => Products::count(),
            'total_orders' => Orders::count(),
            'pending_sellers' => SellerProfile::where('verified', false)->count(),
            'monthly_revenue' => Orders::where('created_at', '>=', Carbon::now()->startOfMonth())
                ->sum('total_price'),
        ];

        // Data untuk grafik pendaftaran user (30 hari terakhir)
        $data['user_signups'] = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Aktivitas terbaru
        $data['latest_users'] = User::with('role')->latest()->take(5)->get();
        $data['latest_orders'] = Orders::with('user')->latest()->take(5)->get();
        $data['pending_seller_requests'] = SellerProfile::with('user')
            ->where('verified', false)
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.admin', compact('data'));
    }
}