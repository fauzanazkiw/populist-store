<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProductImageSeeder extends Seeder
{
    /**
     * Generate a placeholder image for every product that has no main image,
     * store it on the `public` disk, and register it as the product's main image.
     */
    public function run(): void
    {
        // A small palette so different products get different-looking placeholders.
        $palette = [
            [17, 24, 39],    // slate
            [30, 64, 175],   // blue
            [4, 120, 87],    // green
            [153, 27, 27],   // red
            [146, 64, 14],   // amber
            [88, 28, 135],   // purple
            [15, 118, 110],  // teal
            [190, 24, 93],   // pink
        ];

        Product::whereDoesntHave('images', fn ($q) => $q->where('is_main', true))
            ->get()
            ->each(function (Product $product, int $index) use ($palette) {
                [$r, $g, $b] = $palette[$index % count($palette)];

                $png  = $this->makePlaceholder($product->name, $r, $g, $b);
                $path = "products/{$product->id}/placeholder.png";

                Storage::disk('public')->put($path, $png);

                ProductImage::create([
                    'product_id' => $product->id,
                    'path'       => $path,
                    'alt'        => $product->name,
                    'is_main'    => true,
                ]);
            });
    }

    /**
     * Render an 800x800 PNG with a solid background and the product name centered.
     */
    private function makePlaceholder(string $name, int $r, int $g, int $b): string
    {
        $size = 800;
        $img  = imagecreatetruecolor($size, $size);

        $bg    = imagecolorallocate($img, $r, $g, $b);
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, $size, $size, $bg);

        // Center the product name using the largest built-in GD font.
        $font   = 5;
        $text   = strtoupper($name);
        $textW  = imagefontwidth($font) * strlen($text);
        $textH  = imagefontheight($font);
        $x      = (int) (($size - $textW) / 2);
        $y      = (int) (($size - $textH) / 2);
        imagestring($img, $font, $x, $y, $text, $white);

        ob_start();
        imagepng($img);

        return ob_get_clean();
    }
}
