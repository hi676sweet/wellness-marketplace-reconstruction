<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\Basket;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function product(Product $product): View
    {
        abort_unless($product->published, 404);

        return view('products.show', compact('product'));
    }

    public function orders(Basket $basket): View
    {
        $orders = Order::with('items')->where('owner_token', $basket->ownerToken())->latest('id')->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function order(Order $order, Basket $basket): View
    {
        abort_unless(hash_equals($order->owner_token, $basket->ownerToken()), 404);

        return view('orders.show', ['order' => $order->load('items')]);
    }
}
