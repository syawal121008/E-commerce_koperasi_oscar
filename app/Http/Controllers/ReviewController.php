<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Product;
use App\Models\Products;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * Tampilkan semua review
     */
    public function index($productId)
    {
        try {
            // Try Product first, fallback to Products
            $product = null;
            try {
                $product = Product::findOrFail($productId);
            } catch (\Exception $e) {
                $product = Products::findOrFail($productId);
            }
            
            $reviews = Review::with('user')
                ->where('product_id', $productId)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            
            return view('reviews.index', compact('product', 'reviews'));
        } catch (\Exception $e) {
            Log::error('Review index error: ' . $e->getMessage());
            return redirect()->route('shop.index')
                ->with('error', 'Produk tidak ditemukan');
        }
    }

    /**
     * Simpan review baru dengan media upload
     */
    public function store(Request $request, $productId)
    {
        try {
            Log::info('Review store started', [
                'product_id' => $productId,
                'user_id' => Auth::id(),
                'has_media' => $request->hasFile('media')
            ]);

            // Find product
            $product = null;
            try {
                $product = Product::findOrFail($productId);
            } catch (\Exception $e) {
                $product = Products::findOrFail($productId);
            }
            
            // Validasi input dengan rules yang lebih spesifik
            $validated = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'required|string|min:10|max:1000',
                'media' => 'nullable|file|max:10240|mimes:jpeg,jpg,png,gif,webp,bmp,mp4,webm,ogg,mov,avi,3gp', // 10MB max
            ], [
                'rating.required' => 'Rating harus dipilih',
                'rating.min' => 'Rating minimal 1 bintang',
                'rating.max' => 'Rating maksimal 5 bintang',
                'comment.required' => 'Komentar harus diisi',
                'comment.min' => 'Komentar minimal 10 karakter',
                'comment.max' => 'Komentar maksimal 1000 karakter',
                'media.max' => 'Ukuran file maksimal 10MB',
                'media.mimes' => 'Format file tidak didukung. Gunakan: JPG, PNG, GIF, MP4, WEBM, MOV'
            ]);

            // Cek apakah user sudah pernah review produk ini
            $existingReview = Review::where('user_id', Auth::id())
                                  ->where('product_id', $productId)
                                  ->first();

            if ($existingReview) {
                Log::warning('Duplicate review attempt', [
                    'user_id' => Auth::id(),
                    'product_id' => $productId
                ]);
                return back()->with('error', 'Anda sudah memberikan ulasan untuk produk ini.');
            }

            // Handle file upload
            $mediaPath = null;
            if ($request->hasFile('media')) {
                $file = $request->file('media');
                
                Log::info('Processing media upload', [
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize()
                ]);

                // Validasi tambahan
                if (!$file->isValid()) {
                    Log::error('Invalid file upload');
                    return back()->with('error', 'File yang diupload tidak valid.');
                }

                // Double check file size
                $maxSize = 10 * 1024 * 1024; // 10MB
                if ($file->getSize() > $maxSize) {
                    return back()->with('error', 'Ukuran file terlalu besar. Maksimal 10MB.');
                }

                // Tentukan folder berdasarkan tipe file
                $mimeType = $file->getMimeType();
                $folder = 'reviews';
                
                if (str_starts_with($mimeType, 'image/')) {
                    $folder = 'reviews/images';
                } elseif (str_starts_with($mimeType, 'video/')) {
                    $folder = 'reviews/videos';
                } else {
                    Log::warning('Unsupported media type', ['mime_type' => $mimeType]);
                    return back()->with('error', 'Tipe file tidak didukung.');
                }

                // Create filename with timestamp and unique ID
                $extension = $file->getClientOriginalExtension();
                $filename = 'review_' . time() . '_' . uniqid() . '.' . $extension;
                
                try {
                    // Store file di public disk
                    $mediaPath = $file->storeAs($folder, $filename, 'public');
                    
                    Log::info('Media uploaded paidfully', [
                        'path' => $mediaPath,
                        'full_path' => storage_path('app/public/' . $mediaPath)
                    ]);

                    // Verify file was actually stored
                    if (!Storage::disk('public')->exists($mediaPath)) {
                        Log::error('File not found after upload', ['path' => $mediaPath]);
                        return back()->with('error', 'Gagal menyimpan file. Silakan coba lagi.');
                    }

                } catch (\Exception $uploadException) {
                    Log::error('File upload failed', [
                        'error' => $uploadException->getMessage(),
                        'trace' => $uploadException->getTraceAsString()
                    ]);
                    return back()->with('error', 'Gagal mengupload file: ' . $uploadException->getMessage());
                }
            }

            // Create review dalam database transaction
            DB::beginTransaction();
            try {
                $review = Review::create([
                    'user_id' => Auth::id(),
                    'product_id' => $productId,
                    'rating' => $validated['rating'],
                    'comment' => $validated['comment'],
                    'media_path' => $mediaPath,
                    'helpful_count' => 0,
                    'verified_purchase' => $this->isVerifiedPurchase(Auth::id(), $productId),
                ]);

                DB::commit();

                Log::info('Review created paidfully', [
                    'review_id' => $review->review_id,
                    'user_id' => Auth::id(),
                    'product_id' => $productId,
                    'has_media' => !is_null($mediaPath),
                    'media_path' => $mediaPath
                ]);

                return back()->with('paid', 'Ulasan berhasil ditambahkan!');

            } catch (\Exception $dbException) {
                DB::rollback();
                
                // Hapus file yang sudah diupload jika ada error database
                if ($mediaPath && Storage::disk('public')->exists($mediaPath)) {
                    Storage::disk('public')->delete($mediaPath);
                }
                
                throw $dbException;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Review validation failed', [
                'errors' => $e->errors(),
                'user_id' => Auth::id(),
                'product_id' => $productId
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Review Store Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'product_id' => $productId
            ]);
            return back()->with('error', 'Terjadi kesalahan saat menyimpan ulasan. Silakan coba lagi.');
        }
    }

    /**
     * Update review
     */
    public function update(Request $request, $reviewId)
    {
        try {
            $review = Review::findOrFail($reviewId);
            
            // Pastikan hanya pemilik review yang bisa edit
            if ($review->user_id !== Auth::id()) {
                return back()->with('error', 'Anda tidak memiliki akses untuk mengedit ulasan ini.');
            }

            $validated = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'required|string|min:10|max:1000',
                'media' => 'nullable|file|max:10240|mimes:jpeg,jpg,png,gif,webp,bmp,mp4,webm,ogg,mov,avi,3gp',
            ]);

            $oldMediaPath = $review->media_path;

            // Handle file upload jika ada file baru
            if ($request->hasFile('media')) {
                $file = $request->file('media');
                $mimeType = $file->getMimeType();
                
                $folder = str_starts_with($mimeType, 'image/') ? 'reviews/images' : 'reviews/videos';
                
                if (!str_starts_with($mimeType, 'image/') && !str_starts_with($mimeType, 'video/')) {
                    return back()->with('error', 'Tipe file tidak didukung.');
                }

                $extension = $file->getClientOriginalExtension();
                $filename = 'review_' . time() . '_' . uniqid() . '.' . $extension;
                $mediaPath = $file->storeAs($folder, $filename, 'public');
                
                $review->media_path = $mediaPath;
                
                // Hapus file lama setelah upload berhasil
                if ($oldMediaPath && Storage::disk('public')->exists($oldMediaPath)) {
                    Storage::disk('public')->delete($oldMediaPath);
                }
            }

            $review->rating = $validated['rating'];
            $review->comment = $validated['comment'];
            $review->save();

            Log::info('Review updated paidfully', [
                'review_id' => $review->review_id,
                'user_id' => Auth::id()
            ]);

            return back()->with('paid', 'Ulasan berhasil diperbarui!');

        } catch (\Exception $e) {
            Log::error('Review Update Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memperbarui ulasan.');
        }
    }

    /**
     * Hapus review
     */
    public function destroy($reviewId)
    {
        try {
            $review = Review::findOrFail($reviewId);
            
            // Pastikan hanya pemilik review atau admin yang bisa hapus
            if ($review->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return back()->with('error', 'Anda tidak memiliki akses untuk menghapus ulasan ini.');
            }

            // Hapus file dari storage kalau ada
            if ($review->media_path && Storage::disk('public')->exists($review->media_path)) {
                Storage::disk('public')->delete($review->media_path);
            }

            $review->delete();

            Log::info('Review deleted paidfully', [
                'review_id' => $reviewId,
                'deleted_by' => Auth::id()
            ]);

            return back()->with('paid', 'Ulasan berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Review Delete Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus ulasan.');
        }
    }

    /**
     * Toggle helpful vote
     */
    public function toggleHelpful(Request $request, $reviewId)
    {
        try {
            $review = Review::findOrFail($reviewId);
            $userId = Auth::id();
            
            // Check if user already voted
            $existingVote = DB::table('review_helpful')
                ->where('review_id', $reviewId)
                ->where('user_id', $userId)
                ->first();
                
            if ($existingVote) {
                // Remove vote
                DB::table('review_helpful')
                    ->where('review_id', $reviewId)
                    ->where('user_id', $userId)
                    ->delete();
                $review->decrement('helpful_count');
                $helpful = false;
            } else {
                // Add vote
                DB::table('review_helpful')->insert([
                    'review_id' => $reviewId,
                    'user_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $review->increment('helpful_count');
                $helpful = true;
            }
            
            return response()->json([
                'paid' => true,
                'helpful' => $helpful,
                'count' => $review->fresh()->helpful_count
            ]);
            
        } catch (\Exception $e) {
            Log::error('Toggle helpful error: ' . $e->getMessage());
            return response()->json([
                'paid' => false,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }

    /**
     * Cek apakah pembelian terverifikasi
     */
    private function isVerifiedPurchase($userId, $productId)
    {
        try {
            // Check if user has completed order with this product
            $hasOrder = DB::table('orders')
                ->join('order_items', 'orders.order_id', '=', 'order_items.order_id')
                ->where('orders.user_id', $userId)
                ->where('order_items.product_id', $productId)
                ->whereIn('orders.status', ['paid', 'paid'])
                ->exists();
                
            return $hasOrder;
        } catch (\Exception $e) {
            Log::error('Error checking verified purchase: ' . $e->getMessage());
            return false;
        }
    }
}