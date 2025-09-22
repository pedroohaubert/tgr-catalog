<?php

use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/produtos');

// Dashboard route removed in favor of public products page

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Public catalog page
Route::get('/produtos', [ProductController::class, 'page'])->name('products.index');
Route::get('/produtos/busca', [ProductController::class, 'index'])->name('products.search');
Route::get('/produtos/{slug}', [ProductController::class, 'show'])->name('products.show');

// Cart + Checkout (JSON, require auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/carrinho/summary', [CartController::class, 'summary'])->name('cart.summary');
    Route::post('/carrinho/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/carrinho/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/carrinho/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/carrinho/clear', [CartController::class, 'clear'])->name('cart.clear');

    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
});

// User orders (JSON, require auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/meus-pedidos', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/meus-pedidos/{order}/cancelar', [OrderController::class, 'cancel'])->name('orders.cancel');
});

// Admin (JSON, require auth + policy)
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    // Products
    Route::get('/products', [AdminProductController::class, 'index'])
        ->middleware('can:viewAny,App\\Models\\Product')->name('products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])
        ->middleware('can:create,App\\Models\\Product')->name('products.create');
    Route::get('/products/{product}', [AdminProductController::class, 'show'])
        ->middleware('can:view,product')->name('products.show');
    Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])
        ->middleware('can:update,product')->name('products.edit');
    Route::post('/products', [AdminProductController::class, 'store'])
        ->middleware('can:create,App\\Models\\Product')->name('products.store');
    Route::patch('/products/{product}', [AdminProductController::class, 'update'])
        ->middleware('can:update,product')->name('products.update');
    Route::post('/products/{product}/toggle-active', [AdminProductController::class, 'toggleActive'])
        ->middleware('can:update,product')->name('products.toggleActive');

    // Orders
    Route::get('/orders', [AdminOrderController::class, 'index'])
        ->middleware('can:viewAny,App\\Models\\Order')->name('orders.index');
    Route::post('/orders/{order}/pay', [AdminOrderController::class, 'pay'])
        ->middleware('can:pay,order')->name('orders.pay');
    Route::post('/orders/{order}/cancel', [AdminOrderController::class, 'cancel'])
        ->middleware('can:cancel,order')->name('orders.cancel');
});

require __DIR__.'/auth.php';
