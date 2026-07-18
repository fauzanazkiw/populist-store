<?php

use App\Http\Controllers\Web\Admin\CartController as AdminCartController;
use App\Http\Controllers\Web\Admin\CustomerController;
use App\Http\Controllers\Web\Admin\DashboardController;
use App\Http\Controllers\Web\Admin\PaymentController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\ProductController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $featuredProducts = Product::with(['mainImage', 'category'])
        ->where('active', true)
        ->latest()
        ->take(8)
        ->get();

    return view('home', compact('featuredProducts'));
})->name('home');

// About
Route::view('/about', 'about')->name('about');

// Auth
Route::prefix('auth')->group(function () {
    Route::get('/auth/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'store'])->name('register.store');
});

Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public product catalog
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

// User cart (requires authentication)
Route::middleware('auth')->prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add/{product}', [CartController::class, 'add'])->name('add');
    Route::patch('/update/{cartItem}', [CartController::class, 'update'])->name('update');
    Route::delete('/remove/{cartItem}', [CartController::class, 'remove'])->name('remove');
    Route::get('/checkout', [CartController::class, 'checkoutShow'])->name('checkout.show');
    Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout');
});

// User orders & payment (requires authentication)
Route::middleware('auth')->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/pay', [CartController::class, 'retryPayment'])->name('orders.pay');
});

// Admin panel (requires authentication and admin role)
Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Products
    Route::get('/products', [ProductController::class, 'adminIndex'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'adminCreate'])->name('products.create');
    Route::post('/products', [ProductController::class, 'adminStore'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'adminEdit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'adminUpdate'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'adminDestroy'])->name('products.destroy');
    Route::patch('/products/{product}/toggle-active', [ProductController::class, 'adminToggleActive'])->name('products.toggle-active');

    // Customers
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    // Carts
    Route::get('/carts', [AdminCartController::class, 'index'])->name('carts.index');
    Route::get('/carts/{user}', [AdminCartController::class, 'show'])->name('carts.show');

    // Payments
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{order}', [PaymentController::class, 'show'])->name('payments.show');
    Route::patch('/payments/{order}/status', [PaymentController::class, 'updateStatus'])->name('payments.update-status');
});
