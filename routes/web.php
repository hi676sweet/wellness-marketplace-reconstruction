<?php

use App\Http\Controllers\BasketController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\StorefrontController;
use App\Livewire\Shop;
use Illuminate\Support\Facades\Route;

Route::get('/', Shop::class)->name('catalog');
Route::get('/products/{product:slug}', [StorefrontController::class, 'product'])->name('products.show');
Route::get('/basket', [BasketController::class, 'show'])->name('basket.show');
Route::post('/basket/items', [BasketController::class, 'add'])->name('basket.add')->block(10, 10);
Route::patch('/basket/items/{product}', [BasketController::class, 'update'])->name('basket.update')->block(10, 10);
Route::delete('/basket/items/{product}', [BasketController::class, 'remove'])->name('basket.remove')->block(10, 10);
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store')->block(10, 10);
Route::get('/orders', [StorefrontController::class, 'orders'])->name('orders.index');
Route::get('/orders/{order:reference}', [StorefrontController::class, 'order'])->name('orders.show');
