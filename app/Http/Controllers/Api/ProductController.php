<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Api\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(Request $request): JsonResponse
    {
        $filters = array_merge($request->only([
            'search',
            'category_id',
            'min_price',
            'max_price',
            'in_stock',
            'sort_by',
            'sort_dir',
            'per_page',
        ]), ['active' => true]);

        $products = $this->productService->paginate($filters);

        return response()->json([
            'status' => 'success',
            'data' => $products,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->productService->findById($id);

        return response()->json([
            'status' => 'success',
            'data' => $product,
        ]);
    }

    public function adminIndex(Request $request): JsonResponse
    {
        $products = $this->productService->paginate($request->only([
            'search',
            'category_id',
            'active',
            'min_price',
            'max_price',
            'in_stock',
            'sort_by',
            'sort_dir',
            'per_page',
        ]));

        return response()->json([
            'status' => 'success',
            'data' => $products,
        ]);
    }

    public function store(Request $request): JsonResponse
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

        $imageFiles = $request->file('images', []);
        $product = $this->productService->store(
            Arr::except($data, ['images']),
            $imageFiles,
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil dibuat.',
            'data' => $product,
        ], 201);
    }

    public function update(Request $request, Product $product): JsonResponse
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

        $imageFiles = $request->file('images', []);
        $product = $this->productService->update(
            $product,
            Arr::except($data, ['images']),
            $imageFiles,
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil diupdate.',
            'data' => $product,
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        try {
            $this->productService->destroy($product);
        } catch (\RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil dihapus.',
        ]);
    }

    public function toggleActive(Product $product): JsonResponse
    {
        $product = $this->productService->toggleActive($product);

        return response()->json([
            'status' => 'success',
            'message' => $product->active ? 'Produk diaktifkan.' : 'Produk dinonaktifkan.',
            'data' => $product,
        ]);
    }

    public function storeImages(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,png,webp|max:2048',
        ]);

        $this->productService->storeImages($product, $request->file('images'));
        $product->load('images');

        return response()->json([
            'status' => 'success',
            'message' => 'Gambar berhasil diupload.',
            'data' => $product->images,
        ], 201);
    }

    public function setMainImage(Product $product, ProductImage $image): JsonResponse
    {
        if ($image->product_id !== $product->id) {
            return response()->json(['status' => 'error', 'message' => 'Gambar tidak ditemukan.'], 404);
        }

        $this->productService->setMainImage($image);

        return response()->json([
            'status' => 'success',
            'message' => 'Gambar utama berhasil diubah.',
        ]);
    }

    public function deleteImage(Product $product, ProductImage $image): JsonResponse
    {
        if ($image->product_id !== $product->id) {
            return response()->json(['status' => 'error', 'message' => 'Gambar tidak ditemukan.'], 404);
        }

        $this->productService->deleteImage($image);

        return response()->json([
            'status' => 'success',
            'message' => 'Gambar berhasil dihapus.',
        ]);
    }

    public function deductStock(Request $request, Product $product): JsonResponse
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        try {
            $this->productService->deductStock($product, $request->integer('quantity'));
        } catch (InsufficientStockException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'available' => $e->getAvailable(),
                'requested' => $e->getRequested(),
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Stok berhasil dikurangi.',
            'data' => ['stock' => $product->fresh()->stock],
        ]);
    }

    public function restoreStock(Request $request, Product $product): JsonResponse
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        $this->productService->restoreStock($product, $request->integer('quantity'));

        return response()->json([
            'status' => 'success',
            'message' => 'Stok berhasil dikembalikan.',
            'data' => ['stock' => $product->fresh()->stock],
        ]);
    }
}
