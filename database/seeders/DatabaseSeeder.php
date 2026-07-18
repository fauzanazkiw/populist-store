<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // create an admin user (only from dev - cannot register via API)
        if (!User::where("email", "admin@example.com")->exists()) {
            User::create([
                "name" => "Aldi Indra Nugraha",
                "email" => "aldi@admin.com",
                "password" => Hash::make("populist123"),
                "is_admin" => true,
            ]);
        }

        // create a test customer (can register via API/Web)
        if (!User::where("email", "test@example.com")->exists()) {
            User::create([
                "name" => "Test Customer",
                "email" => "test@example.com",
                "password" => Hash::make("customer123456"),
                "is_admin" => false,
            ]);
        }

        // categories
        $women = Category::firstOrCreate(
            ["slug" => "womens-clothing"],
            ["name" => "Women's Clothing", "description" => "Women collection"],
        );

        $men = Category::firstOrCreate(
            ["slug" => "mens-clothing"],
            ["name" => "Men's Clothing", "description" => "Men collection"],
        );

        $accessories = Category::firstOrCreate(
            ["slug" => "accessories"],
            ["name" => "Accessories", "description" => "Accessories & more"],
        );

        // products
        $items = [
            [
                "name" => "Basic Tee",
                "price" => 129000,
                "stock" => 40,
                "category" => $men,
            ],
            [
                "name" => "Women Dress",
                "price" => 299000,
                "stock" => 20,
                "category" => $women,
            ],
            [
                "name" => "Beanie Hat",
                "price" => 89000,
                "stock" => 60,
                "category" => $accessories,
            ],
            [
                "name" => "Casual Shirt",
                "price" => 159000,
                "stock" => 35,
                "category" => $men,
            ],
            [
                "name" => "Denim Jacket",
                "price" => 499000,
                "stock" => 15,
                "category" => $women,
            ],
            [
                "name" => "Black Tee",
                "price" => 109000,
                "stock" => 50,
                "category" => $men,
            ],
            [
                "name" => "Summer Blouse",
                "price" => 189000,
                "stock" => 25,
                "category" => $women,
            ],
            [
                "name" => "Leather Belt",
                "price" => 99000,
                "stock" => 80,
                "category" => $accessories,
            ],
        ];

        foreach ($items as $it) {
            Product::firstOrCreate(
                [
                    "slug" =>
                    Str::slug($it["name"]) .
                        "-" .
                        substr(Str::random(8), 0, 6),
                ],
                [
                    "name" => $it["name"],
                    "description" => $it["name"] . " description",
                    "price" => $it["price"],
                    "stock" => $it["stock"],
                    "category_id" => $it["category"]->id,
                    "active" => true,
                ],
            );
        }

        // Give every product a main image so the storefront and admin
        // list render a picture instead of the empty placeholder.
        $this->call(ProductImageSeeder::class);
    }
}
