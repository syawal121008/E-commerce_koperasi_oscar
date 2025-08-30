<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    /**
     * Display shop products listing
     */
    public function index(Request $request)
    {
        try {
            $query = Product::with(['admin', 'category'])
                ->active();

            // Apply search filter
            if ($request->has('search') && $request->search != '') {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            }

            // Apply category filter - FIXED: use 'category' instead of 'category_id'
            if ($request->has('category') && $request->category != '') {
                $query->where('category_id', $request->category);
            }

            // Apply admin filter
            if ($request->has('admin_id') && $request->admin_id != '') {
                $query->where('admin_id', $request->admin_id);
            }

            // Apply stock filter
            if ($request->boolean('in_stock')) {
                $query->inStock();
            }

            // FIXED: Sorting options to match the view
            $sortBy = $request->get('sort', 'newest');
            switch ($sortBy) {
                case 'price_asc': // matches view
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc': // matches view
                    $query->orderBy('price', 'desc');
                    break;
                case 'name_asc': // matches view
                    $query->orderBy('name', 'asc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                default: // newest
                    $query->orderBy('created_at', 'desc');
                    break;
            }

            // Get products with pagination
            $products = $query->paginate(12);
            
            // Get all active categories
            $categories = Category::active()->get();

            // Add review data to each product
            $products->getCollection()->transform(function ($product) {
                $product->avg_rating = Review::where('product_id', $product->product_id)
                    ->avg('rating') ?? 0;
                $product->reviews_count = Review::where('product_id', $product->product_id)
                    ->count();
                return $product;
            });

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'data' => [
                        'products' => $products,
                        'categories' => $categories
                    ]
                ]);
            }

            // Try different view paths - adjust this based on your actual file structure
            if (view()->exists('shop.index')) {
                return view('shop.index', compact('products', 'categories'));
            } elseif (view()->exists('shop.products.index')) {
                return view('shop.products.index', compact('products', 'categories'));
            } else {
                // Fallback - return a simple response to debug
                return response()->json([
                    'message' => 'View not found. Available data:',
                    'products_count' => $products->count(),
                    'categories_count' => $categories->count(),
                    'view_paths_tried' => ['shop.index', 'shop.products.index']
                ]);
            }

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'message' => 'Gagal mengambil data produk',
                    'error' => $e->getMessage()
                ], 500);
            }

            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Display the specified product in shop with reviews
     */
    public function show($id)
    {
        try {
            $product = Product::with(['admin', 'category'])
                ->findOrFail($id);

            // Get reviews with user data, ordered by most recent
            $reviews = Review::with('user')
                ->where('product_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Calculate review statistics
            $reviewsCount = $reviews->count();
            $avgRating = $reviewsCount > 0 ? round($reviews->avg('rating'), 1) : 0;

            // Count ratings distribution (1-5 stars)
            $ratingsCount = [];
            for ($i = 1; $i <= 5; $i++) {
                $ratingsCount[$i] = $reviews->where('rating', $i)->count();
            }

            // Calculate percentages for rating bars
            $ratingsPercentage = [];
            for ($i = 1; $i <= 5; $i++) {
                $ratingsPercentage[$i] = $reviewsCount > 0 ? 
                    round(($ratingsCount[$i] / $reviewsCount) * 100, 1) : 0;
            }

            // Check if current user has reviewed this product
            $hasReviewed = false;
            $userReview = null;
            if (Auth::check()) {
                $userReview = Review::where('user_id', Auth::id())
                    ->where('product_id', $id)
                    ->first();
                $hasReviewed = $userReview !== null;
            }

            // Get related products (same category, different product)
            $relatedProducts = Product::with(['category'])
                ->where('category_id', $product->category_id)
                ->where('product_id', '!=', $id)
                ->active()
                ->inStock()
                ->limit(4)
                ->get();

            // Add review data to related products
            $relatedProducts->each(function($relatedProduct) {
                $relatedProduct->avg_rating = Review::where('product_id', $relatedProduct->product_id)
                    ->avg('rating') ?? 0;
                $relatedProduct->reviews_count = Review::where('product_id', $relatedProduct->product_id)
                    ->count();
            });

            if (request()->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'data' => [
                        'product' => $product,
                        'reviews' => $reviews,
                        'reviewsCount' => $reviewsCount,
                        'avgRating' => $avgRating,
                        'ratingsCount' => $ratingsCount,
                        'ratingsPercentage' => $ratingsPercentage,
                        'hasReviewed' => $hasReviewed,
                        'userReview' => $userReview,
                        'relatedProducts' => $relatedProducts
                    ]
                ]);
            }

            // Try different view paths for show
            if (view()->exists('shop.show')) {
                return view('shop.show', compact(
                    'product', 'reviews', 'reviewsCount', 'avgRating', 'ratingsCount',
                    'ratingsPercentage', 'hasReviewed', 'userReview', 'relatedProducts'
                ));
            } elseif (view()->exists('shop.products.show')) {
                return view('shop.products.show', compact(
                    'product', 'reviews', 'reviewsCount', 'avgRating', 'ratingsCount',
                    'ratingsPercentage', 'hasReviewed', 'userReview', 'relatedProducts'
                ));
            } else {
                return response()->json([
                    'message' => 'Product view not found',
                    'product' => $product->name
                ]);
            }

        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'message' => 'Produk tidak ditemukan',
                    'error' => $e->getMessage()
                ], 404);
            }

            return redirect()->route('shop.index')
                ->with('error', 'Produk tidak ditemukan');
        }
    }
}