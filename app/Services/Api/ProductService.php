<?php

namespace App\Services\Api;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $allowedSorts = ['name', 'price', 'stock', 'created_at'];
        $sortBy  = in_array($filters['sort_by'] ?? '', $allowedSorts) ? $filters['sort_by'] : 'created_at';
        $sortDir = strtolower($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return Product::with(['category', 'mainImage'])
            ->when(
                !empty($filters['search']),
                fn($q) => $q->where(function ($inner) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $inner->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term);
                })
            )
            ->when(!empty($filters['category_id']), fn($q) => $q->where('category_id', $filters['category_id']))
            ->when(isset($filters['active']),        fn($q) => $q->where('active', (bool) $filters['active']))
            ->when(!empty($filters['min_price']),    fn($q) => $q->where('price', '>=', $filters['min_price']))
            ->when(!empty($filters['max_price']),    fn($q) => $q->where('price', '<=', $filters['max_price']))
            ->when(!empty($filters['in_stock']),     fn($q) => $q->where('stock', '>', 0))
            ->orderBy($sortBy, $sortDir)
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function findById(int $id): Product
    {
        return Product::with(['category', 'images'])->findOrFail($id);
    }

    public function findBySlug(string $slug): Product
    {
        return Product::with(['category', 'images'])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function store(array $data, array $imageFiles = []): Product
    {
        return DB::transaction(function () use ($data, $imageFiles) {
            $data['slug'] = $this->generateUniqueSlug($data['name']);

            $product = Product::create($data);

            if ($imageFiles) {
                $this->storeImages($product, $imageFiles, setFirstAsMain: true);
            }

            return $product->load(['category', 'images']);
        });
    }

    public function update(Product $product, array $data, array $imageFiles = []): Product
    {
        return DB::transaction(function () use ($product, $data, $imageFiles) {
            if (isset($data['name']) && $data['name'] !== $product->name) {
                $data['slug'] = $this->generateUniqueSlug($data['name'], $product->id);
            }

            $product->update($data);

            if ($imageFiles) {
                $this->storeImages($product, $imageFiles);
            }

            return $product->fresh(['category', 'images']);
        });
    }

    public function destroy(Product $product): void
    {
        $hasActiveOrders = $product->orderItems()
            ->whereHas('order', fn($q) => $q->whereNotIn('status', ['completed', 'cancelled']))
            ->exists();

        if ($hasActiveOrders) {
            throw new \RuntimeException(
                "Cannot delete \"{$product->name}\" because it has active orders."
            );
        }

        DB::transaction(function () use ($product) {
            // Remove image files from storage before deleting records
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->path);
            }

            $product->delete();
        });
    }

    public function deductStock(Product $product, int $quantity): void
    {
        if ($product->stock < $quantity) {
            throw new InsufficientStockException(
                "Insufficient stock for \"{$product->name}\". Available: {$product->stock}, requested: {$quantity}.",
                available: $product->stock,
                requested: $quantity,
            );
        }

        $product->decrement('stock', $quantity);
    }

    public function restoreStock(Product $product, int $quantity): void
    {
        $product->increment('stock', $quantity);
    }

    public function toggleActive(Product $product): Product
    {
        $product->update(['active' => !$product->active]);

        return $product;
    }

    public function storeImages(Product $product, array $files, bool $setFirstAsMain = false): void
    {
        $hasMainImage = $product->mainImage()->exists();

        foreach ($files as $index => $file) {
            $path    = $file->store("products/{$product->id}", 'public');
            $isMain  = !$hasMainImage && $setFirstAsMain && $index === 0;

            ProductImage::create([
                'product_id' => $product->id,
                'path'       => $path,
                'alt'        => $product->name,
                'is_main'    => $isMain,
            ]);

            if ($isMain) {
                $hasMainImage = true;
            }
        }
    }

    public function setMainImage(ProductImage $image): void
    {
        DB::transaction(function () use ($image) {
            ProductImage::where('product_id', $image->product_id)->update(['is_main' => false]);
            $image->update(['is_main' => true]);
        });
    }

    public function deleteImage(ProductImage $image): void
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();
    }


    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base  = Str::slug($name);
        $slug  = $base;
        $count = 1;

        while (
            Product::where('slug', $slug)
            ->when($excludeId !== null, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists()
        ) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }
}
