<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        try {
            $query = Product::with(['admin', 'category']);

            if ($user->role === 'admin') {
                $query->where('admin_id', $user->user_id);
            }

            $statsQuery = clone $query;
            $totalProducts = $statsQuery->count();
            $activeProducts = (clone $statsQuery)->where('is_active', true)->count();
            $lowStockProducts = (clone $statsQuery)->where('stock', '<', 10)->where('stock', '>', 0)->count();
            $outOfStockProducts = (clone $statsQuery)->where('stock', 0)->count();
            $inactiveProducts = (clone $statsQuery)->where('is_active', false)->count();

            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }
            
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->filled('status')) {
                switch ($request->status) {
                    case 'low_stock':
                        $query->where('stock', '<', 10)->where('stock', '>', 0);
                        break;
                    case 'out_of_stock':
                        $query->where('stock', 0);
                        break;
                    case 'inactive':
                        $query->where('is_active', false);
                        break;
                    case 'active':
                        $query->where('is_active', true);
                        break;
                }
            }

            $products = $query->orderBy('created_at', 'desc')->paginate(9);
            $categories = Category::active()->get();

            return view('products.index', compact(
                'products', 
                'categories',
                'totalProducts',
                'activeProducts',
                'lowStockProducts',
                'outOfStockProducts',
                'inactiveProducts'
            ));

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal mengambil data produk: ' . $e->getMessage()]);
        }
    }

    public function create(Request $request)
    {
        if ($request->user()->role === 'customer') {
            abort(403, 'AKSES DITOLAK.');
        }
        $categories = Category::active()->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'modal_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:category,category_id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $data = $request->all();
            $data['admin_id'] = $request->user()->user_id;

            if ($data['stock'] == 0) {
                $data['is_active'] = false;
            }

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
                $data['image'] = $imagePath;
                $data['image_url'] = Storage::url($imagePath);
            }

            $product = Product::create($data);

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'message' => 'Produk berhasil dibuat',
                    'data' => $product->load('category')
                ], 201);
            }

            return redirect()->route('products.index')
                ->with('paid', 'Produk berhasil dibuat');

        } catch (\Exception $e) {
            return response()->json([
                'paid' => false,
                'message' => 'Gagal membuat produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        if ($request->user()->role === 'customer') {
            abort(403, 'AKSES DITOLAK.');
        }
        try {
            $product = Product::with(['admin', 'category'])
                ->findOrFail($id);

            if (request()->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'data' => $product
                ]);
            }

            return view('products.show', compact('product'));

        } catch (\Exception $e) {
            return response()->json([
                'paid' => false,
                'message' => 'Produk tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->role === 'customer') {
            abort(403, 'AKSES DITOLAK.');
        }
        $product = Product::findOrFail($id);
        
        if ($product->admin_id !== auth()->user()->user_id && auth()->user()->role !== 'admin') {
            abort(403, 'Tidak diizinkan');
        }

        $categories = Category::active()->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if ($product->admin_id !== $request->user()->user_id && 
            $request->user()->role !== 'admin') {
            return response()->json([
                'paid' => false,
                'message' => 'Tidak diizinkan'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'modal_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:category,category_id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $data = $request->all();

            if ($data['stock'] == 0) {
                $data['is_active'] = false;
            }

            if ($request->hasFile('image')) {
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $imagePath = $request->file('image')->store('products', 'public');
                $data['image'] = $imagePath;
                $data['image_url'] = Storage::url($imagePath);
            }

            $product->update($data);

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'message' => 'Produk berhasil diperbarui',
                    'data' => $product->load('category')
                ]);
            }

            return redirect()->route('products.index')
                ->with('paid', 'Produk berhasil diperbarui');

        } catch (\Exception $e) {
            return response()->json([
                'paid' => false,
                'message' => 'Gagal memperbarui produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->user()->role === 'customer') {
            abort(403, 'AKSES DITOLAK.');
        }
        try {
            $product = Product::findOrFail($id);
            
            // Check if user is the admin or admin
            if ($product->admin_id !== $request->user()->user_id && 
                $request->user()->role !== 'admin') {
                return response()->json([
                    'paid' => false,
                    'message' => 'Tidak diizinkan'
                ], 403);
            }

            // Delete image
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'message' => 'Produk berhasil dihapus'
                ]);
            }

            return redirect()->route('products.index')
                ->with('paid', 'Produk berhasil dihapus');

        } catch (\Exception $e) {
            return response()->json([
                'paid' => false,
                'message' => 'Gagal menghapus produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function myProducts(Request $request)
    {
        try {
            $products = Product::with('category')
                ->where('admin_id', $request->user()->user_id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'data' => $products
                ]);
            }

            return view('products.my-products', compact('products'));

        } catch (\Exception $e) {
            return response()->json([
                'paid' => false,
                'message' => 'Gagal mengambil data produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            
            if ($product->admin_id !== $request->user()->user_id && 
                $request->user()->role !== 'admin') {
                return response()->json([
                    'paid' => false,
                    'message' => 'Tidak diizinkan'
                ], 403);
            }

            if (!$product->is_active && $product->stock == 0) {
                return response()->json([
                    'paid' => false,
                    'message' => 'Produk tidak dapat diaktifkan karena stok habis'
                ], 400);
            }

            $product->is_active = !$product->is_active;
            $product->save();

            $status = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'message' => "Produk berhasil $status",
                    'data' => $product
                ]);
            }

            return back()->with('paid', "Produk berhasil $status");

        } catch (\Exception $e) {
            return response()->json([
                'paid' => false,
                'message' => 'Gagal mengubah status produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function profitReport(Request $request)
    {   
        $products = Product::with('category')->get();

        $products->map(function ($product) {
            $product->profit_per_item = $product->price - $product->modal_price;
            $product->total_profit = $product->profit_per_item * $product->stock;
            return $product;
        });

        return view('products.profit-report', compact('products'));
    }
}