<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderReceiptController extends Controller
{
    /**
     * Show receipt page - FIXED: Tidak menggunakan auto print atau redirect ke tab baru
     */
    public function show(Request $request, $orderId)
    {
        try {
            $order = Order::with(['items.product', 'customer', 'admin', 'transaction'])
                ->findOrFail($orderId);

            // Check authorization - only customer, admin, or admin can view receipt
            if ($order->customer_id !== $request->user()->user_id && 
                $order->admin_id !== $request->user()->user_id &&
                $request->user()->role !== 'admin') {
                
                if ($request->expectsJson()) {
                    return response()->json(['paid' => false, 'message' => 'Tidak diizinkan'], 403);
                }
                abort(403, 'AKSES DITOLAK.');
            }

            // Only show receipt for paid or completed orders
            if (!in_array($order->status, ['paid', 'paid'])) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'paid' => false, 
                        'message' => 'Struk hanya tersedia untuk pesanan yang sudah dibayar'
                    ], 400);
                }
                return redirect()->route('orders.show', $orderId)
                    ->with('error', 'Struk hanya tersedia untuk pesanan yang sudah dibayar');
            }

            // FIXED: Tidak lagi menggunakan parameter print untuk auto print
            // Langsung tampilkan halaman receipt normal
            return view('orders.receipt', compact('order'));

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'message' => 'Pesanan tidak ditemukan',
                    'error' => $e->getMessage()
                ], 404);
            }
            return redirect()->route('orders.index')->with('error', 'Pesanan tidak ditemukan');
        }
    }

    /**
     * Download receipt as PDF - Optimized for thermal printer
     */
    public function download(Request $request, $orderId)
    {
        try {
            $order = Order::with(['items.product', 'customer', 'admin', 'transaction'])
                ->findOrFail($orderId);

            // Check authorization
            if ($order->customer_id !== $request->user()->user_id && 
                $order->admin_id !== $request->user()->user_id &&
                $request->user()->role !== 'admin') {
                abort(403, 'AKSES DITOLAK.');
            }

            // Only allow download for paid or completed orders
            if (!in_array($order->status, ['paid', 'paid'])) {
                return redirect()->route('orders.show', $orderId)
                    ->with('error', 'Struk hanya tersedia untuk pesanan yang sudah dibayar');
            }

            // Generate PDF dengan pengaturan yang diperbaiki
            $pdf = PDF::loadView('orders.receipt-pdf', compact('order'));
            
            // Set paper size untuk thermal printer 80mm (precise measurements)
            // 80mm = 226.77 points, tinggi auto sesuai content
            $pdf->setPaper([0, 0, 226.77, 'auto'], 'portrait');
            
            // Set PDF options untuk hasil yang lebih baik
            $pdf->setOptions([
                'dpi' => 200,
                'defaultFont' => 'DejaVu Sans',
                'fontSubsetting' => false,
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => false,
                'isRemoteEnabled' => false,
                'debugKeepTemp' => false,
                'debugCss' => false,
                'debugLayout' => false,
                'debugLayoutLines' => false,
                'debugLayoutBlocks' => false,
                'debugLayoutInline' => false,
                'debugLayoutPaddingBox' => false,
            ]);
            
            // Set filename
            $filename = 'struk-pesanan-' . substr($order->order_id, 0, 8) . '-' . now()->format('YmdHis') . '.pdf';
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            return redirect()->route('orders.index')->with('error', 'Gagal mengunduh struk: ' . $e->getMessage());
        }
    }

    /**
     * REMOVED: Print method tidak lagi diperlukan karena menggunakan JavaScript print
     */

    /**
     * Get receipt data for modal (API endpoint)
     */
    public function getReceiptData(Request $request, $orderId)
    {
        try {
            $order = Order::with(['items.product', 'customer', 'admin', 'transaction'])
                ->findOrFail($orderId);

            // Check authorization
            if ($order->customer_id !== $request->user()->user_id && 
                $order->admin_id !== $request->user()->user_id &&
                $request->user()->role !== 'admin') {
                
                return response()->json(['paid' => false, 'message' => 'Tidak diizinkan'], 403);
            }

            // Only show receipt for paid or completed orders
            if (!in_array($order->status, ['paid', 'paid'])) {
                return response()->json([
                    'paid' => false, 
                    'message' => 'Struk hanya tersedia untuk pesanan yang sudah dibayar'
                ], 400);
            }

            return response()->json([
                'paid' => true,
                'data' => [
                    'order' => $order,
                    'receipt_html' => view('orders.receipt-modal', compact('order'))->render()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'paid' => false,
                'message' => 'Pesanan tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Generate receipt preview for thermal printer testing
     */
    public function preview(Request $request, $orderId)
    {
        try {
            $order = Order::with(['items.product', 'customer', 'admin', 'transaction'])
                ->findOrFail($orderId);

            // Check authorization
            if ($order->customer_id !== $request->user()->user_id && 
                $order->admin_id !== $request->user()->user_id &&
                $request->user()->role !== 'admin') {
                abort(403, 'AKSES DITOLAK.');
            }

            // Only show receipt for paid or completed orders
            if (!in_array($order->status, ['paid', 'paid'])) {
                return redirect()->route('orders.show', $orderId)
                    ->with('error', 'Struk hanya tersedia untuk pesanan yang sudah dibayar');
            }

            // Generate PDF for preview
            $pdf = PDF::loadView('orders.receipt-pdf', compact('order'));
            
            // Use thermal printer size for preview
            $pdf->setPaper([0, 0, 226.77, 'auto'], 'portrait');
            
            // Stream to browser for preview
            return $pdf->stream('preview-struk-' . substr($order->order_id, 0, 8) . '.pdf');

        } catch (\Exception $e) {
            return redirect()->route('orders.index')->with('error', 'Gagal menampilkan preview: ' . $e->getMessage());
        }
    }
}