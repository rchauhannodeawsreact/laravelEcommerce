<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Category;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Get all products
     */
    public function index(Request $request)
    {
        $query = Product::where('status', 'approved')
            ->with(['images', 'vendor', 'category']);

        // Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->input('vendor_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereRaw('MATCH (name, description) AGAINST (? IN BOOLEAN MODE)', [$search]);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        if ($request->filled('sort')) {
            $sort = $request->input('sort');
            $order = $request->input('order', 'asc');
            $query->orderBy($sort, $order);
        } else {
            $query->latest();
        }

        $products = $query->paginate($request->input('per_page', 15));

        return $this->success($products, 'Products retrieved');
    }

    /**
     * Get single product
     */
    public function show($id)
    {
        $product = Product::with([
            'images',
            'variants',
            'inventory',
            'vendor',
            'reviews' => function ($q) {
                $q->where('status', 'approved')->latest();
            },
        ])->findOrFail($id);

        return $this->success($product, 'Product details retrieved');
    }

    /**
     * Create product (Vendor only)
     */
    public function store(Request $request)
    {
        $this->authorize('isVendor');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'required|string|unique:products',
            'stock' => 'required|integer|min:0',
            'images' => 'array',
            'variants' => 'array',
        ]);

        try {
            $product = $this->productService->createProduct(
                auth()->user()->vendor->id,
                $validated
            );

            return $this->success($product, 'Product created successfully', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Update product (Vendor only)
     */
    public function update(Request $request, $id)
    {
        $this->authorize('isVendor');

        $product = Product::findOrFail($id);
        $this->authorize('update', $product);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'string',
            'price' => 'numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'category_id' => 'exists:categories,id',
        ]);

        try {
            $product = $this->productService->updateProduct($product, $validated);
            return $this->success($product, 'Product updated successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Get categories
     */
    public function categories()
    {
        $categories = Category::where('is_active', true)
            ->with('children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return $this->success($categories, 'Categories retrieved');
    }
}
