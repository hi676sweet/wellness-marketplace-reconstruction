<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Basket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BasketController extends Controller
{
    public function show(Basket $basket): View
    {
        $lines = $basket->lines();

        return view('basket', ['lines' => $lines, 'subtotal_cents' => $lines->sum('total_cents'), 'checkout_key' => $basket->checkoutKey()]);
    }

    public function add(Request $request, Basket $basket): RedirectResponse
    {
        $data = $request->validate(['product_id' => ['required', 'integer', 'exists:products,id'], 'quantity' => ['required', 'integer', 'min:1', 'max:20']]);
        $product = Product::findOrFail($data['product_id']);
        $basket->add($product, (int) $data['quantity']);

        return back()->with('success', $product->name.' was added to your basket.');
    }

    public function update(Request $request, Product $product, Basket $basket): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:20']]);
        $basket->set($product, (int) $data['quantity']);

        return to_route('basket.show')->with('success', 'Basket quantity updated.');
    }

    public function remove(Product $product, Basket $basket): RedirectResponse
    {
        $basket->remove($product);

        return to_route('basket.show')->with('success', 'Product removed from your basket.');
    }
}
