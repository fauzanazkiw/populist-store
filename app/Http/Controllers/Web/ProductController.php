<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\Api\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    // -------------------------------------------------------------------------
    // Public storefront
    // -------------------------------------------------------------------------

    /**
     * GET /products
     */
    public function index(Request $request): View
    {
        $filters = $this->resolveFilters($request, forceActive: true);

        $products = $this->productService->paginate($filters);
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'filters'));
    }

    /**
     * GET /products/{product}
     */
    public function show(Product $product): View
    {
        abort_if(! $product->active, 404);

        $product->load(['category', 'images']);

        return view('products.show', compact('product'));
    }

    // -------------------------------------------------------------------------
    // Admin
    // -------------------------------------------------------------------------

    /**
     * GET /admin/products
     */
    public function adminIndex(Request $request): View
    {
        $filters = $this->resolveFilters($request, forceActive: false);
        $products = $this->productService->paginate($filters);

        return view('admin.products.index', compact('products'));
    }

    /**
     * GET /admin/products/create
     */
    public function adminCreate(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    /**
     * POST /admin/products
     */
    public function adminStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sizes' => 'nullable|array',
            'sizes.*' => ['string', Rule::in(Product::SIZES)],
            'active' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,webp|max:2048',
        ]);

        $this->productService->store(
            Arr::except($data, ['images']),
            $request->file('images', []),
        );

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil dibuat.');
    }

    /**
     * GET /admin/products/{product}/edit
     */
    public function adminEdit(Product $product): View
    {
        $product->load('images');
        $categories = Category::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * PUT /admin/products/{product}
     */
    public function adminUpdate(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'sizes' => 'nullable|array',
            'sizes.*' => ['string', Rule::in(Product::SIZES)],
            'active' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,webp|max:2048',
        ]);

        $this->productService->update(
            $product,
            Arr::except($data, ['images']),
            $request->file('images', []),
        );

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil diupdate.');
    }

    /**
     * DELETE /admin/products/{product}
     */
    public function adminDestroy(Product $product): RedirectResponse
    {
        try {
            $this->productService->destroy($product);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * PATCH /admin/products/{product}/toggle-active
     */
    public function adminToggleActive(Product $product): RedirectResponse
    {
        $updated = $this->productService->toggleActive($product);

        return back()->with(
            'success',
            $updated->active
                ? 'Produk diaktifkan.'
                : 'Produk dinonaktifkan.'
        );
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Build the filters array from the request.
     *
     * The views submit a combined `?sort=` param (e.g. "price_asc", "name_desc",
     * "newest") which we expand into the `sort_by` / `sort_dir` pair that
     * ProductService::paginate() expects.
     *
     * @return array<string, mixed>
     */
    private function resolveFilters(Request $request, bool $forceActive): array
    {
        $filters = $request->only([
            'search',
            'category_id',
            'min_price',
            'max_price',
            'in_stock',
            'per_page',
        ]);

        // Map combined sort param → sort_by + sort_dir
        $sortMap = [
            'newest' => ['created_at', 'desc'],
            'name_asc' => ['name',       'asc'],
            'name_desc' => ['name',       'desc'],
            'price_asc' => ['price',      'asc'],
            'price_desc' => ['price',      'desc'],
        ];
        [$sortBy, $sortDir] = $sortMap[$request->input('sort', 'newest')] ?? ['created_at', 'desc'];
        $filters['sort_by'] = $sortBy;
        $filters['sort_dir'] = $sortDir;
        // Keep raw sort for view pre-selection
        $filters['sort'] = $request->input('sort', 'newest');

        if ($forceActive) {
            $filters['active'] = true;
        } elseif ($request->has('active')) {
            $filters['active'] = $request->boolean('active');
        }

        return $filters;
    }
}
