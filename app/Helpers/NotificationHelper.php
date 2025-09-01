<?php

namespace App\Helpers;

use App\Models\Order;

class NotificationHelper
{
    /**
     * Menghitung jumlah pesanan berdasarkan array status yang diberikan.
     *
     * @param string $adminId
     * @param array $statuses
     * @return int
     */
    public static function getOrdersByStatusCount(string $adminId, array $statuses): int
    {
        return Order::where('admin_id', $adminId)
                    ->whereIn('status', $statuses)
                    ->count();
    }

    /**
     * Mengambil koleksi pesanan berdasarkan array status dengan limit.
     * PERBAIKAN: Tambahkan eager loading untuk customer dan items.product
     *
     * @param string $adminId
     * @param array $statuses
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getOrdersByStatus(string $adminId, array $statuses, int $limit = 5)
    {
        return Order::with([
                'customer:user_id,full_name,email,student_id,profile_photo', // Eager load customer data yang diperlukan
                'items.product:product_id,name' // Eager load product name untuk tampilan
            ])
            ->where('admin_id', $adminId)
            ->whereIn('status', $statuses)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Mendapatkan total pesanan baru (pending & paid) untuk semua admin (untuk super admin)
     */
    public static function getAllNewOrdersCount()
    {
        return Order::whereIn('status', ['pending', 'paid'])->count();
    }

    /**
     * Format notifikasi badge
     */
    public static function formatBadgeCount($count)
    {
        if ($count > 99) {
            return '99+';
        }
        return $count;
    }

    /**
     * Cek apakah user memiliki notifikasi pesanan baru
     */
    public static function hasNewNotifications($user)
    {
        if ($user->role !== 'admin') {
            return false;
        }
        return self::getOrdersByStatusCount($user->user_id, ['pending', 'paid']) > 0;
    }
}