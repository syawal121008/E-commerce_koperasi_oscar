<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index(Request $request)
    {
        try {
            $query = Category::query();

            if ($request->boolean('active_only')) {
                $query->active();
            }

            $categories = $query->withCount('products')->get();

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'data' => $categories
                ]);
            }

            return view('categories.index', compact('categories'));

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'message' => 'Failed to retrieve categories',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Failed to retrieve categories: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new category (Admin only)
     */
    public function create(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Unauthorized. Admin access required.');
        }

        return view('categories.create');
    }

    /**
     * Show the form for editing a category (Admin only)
     */
    public function edit($id)
    {
        $category = Category::findOrFail($id);

        // hanya admin yang bisa akses
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized. Admin access required.');
        }

        return view('categories.edit', compact('category'));
    }

    /**
     * Store a newly created category (Admin only)
     */
    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }
            abort(403, 'Unauthorized. Admin access required.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:category,name',
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
            $categoryData = $request->all();
            // Set default value for is_active if not provided
            if (!isset($categoryData['is_active'])) {
                $categoryData['is_active'] = true;
            }
            
            $category = Category::create($categoryData);

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'message' => 'Category created paidfully',
                    'data' => $category
                ], 201);
            }

            return redirect()->route('categories.index')->with('paid', 'Kategori berhasil ditambahkan!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'message' => 'Failed to create category',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal menambahkan kategori: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update the specified category (Admin only)
     */
    public function update(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }
            abort(403, 'Unauthorized. Admin access required.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:category,name,' . $id . ',category_id',
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
            $category = Category::findOrFail($id);
            $category->update($request->all());

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'message' => 'Category updated paidfully',
                    'data' => $category
                ]);
            }

            return redirect()->route('categories.index')->with('paid', 'Kategori berhasil diperbarui!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'message' => 'Failed to update category',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal memperbarui kategori: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified category (Admin only)
     */
    public function destroy(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }
            abort(403, 'Unauthorized. Admin access required.');
        }

        try {
            $category = Category::findOrFail($id);
            
            // Check if category has products
            if ($category->products()->count() > 0) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'paid' => false,
                        'message' => 'Cannot delete category with existing products'
                    ], 400);
                }
                
                return back()->with('error', 'Tidak dapat menghapus kategori yang masih memiliki produk.');
            }

            $category->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'message' => 'Category deleted paidfully'
                ]);
            }

            return redirect()->route('categories.index')->with('paid', 'Kategori berhasil dihapus!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'message' => 'Failed to delete category',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }
}