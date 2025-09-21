<?php

use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Public catalog (page + JSON data)
Route::get('/produtos/pagina', [ProductController::class, 'page'])->name('products.page');
Route::get('/produtos', [ProductController::class, 'index'])->name('products.index');
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
});

// Admin (JSON, require auth + policy)
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    // Products
    Route::get('/products', [AdminProductController::class, 'index'])
        ->middleware('can:viewAny,App\\Models\\Product')->name('products.index');
    Route::get('/products/{product}', [AdminProductController::class, 'show'])
        ->middleware('can:view,product')->name('products.show');
    Route::post('/products', [AdminProductController::class, 'store'])
        ->middleware('can:create,App\\Models\\Product')->name('products.store');
    Route::patch('/products/{product}', [AdminProductController::class, 'update'])
        ->middleware('can:update,product')->name('products.update');
    Route::post('/products/{product}/toggle-active', [AdminProductController::class, 'toggleActive'])
        ->middleware('can:update,product')->name('products.toggleActive');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])
        ->middleware('can:delete,product')->name('products.destroy');

    // Orders
    Route::get('/orders', [AdminOrderController::class, 'index'])
        ->middleware('can:viewAny,App\\Models\\Order')->name('orders.index');
    Route::post('/orders/{order}/pay', [AdminOrderController::class, 'pay'])
        ->middleware('can:pay,order')->name('orders.pay');
});

require __DIR__.'/auth.php';
