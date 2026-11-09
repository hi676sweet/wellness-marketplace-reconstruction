<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Basket
{
    public const MAX_QUANTITY = 20;

    /** @return array<int,int> */
    public function raw(): array
    {
        return session('cart', []);
    }

    public function ownerToken(): string
    {
        if (! session()->has('shopper_token')) {
            session()->put('shopper_token', (string) Str::uuid());
        }

        return session('shopper_token');
    }

    public function checkoutKey(): string
    {
        if (! session()->has('checkout_key')) {
            session()->put('checkout_key', (string) Str::uuid());
        }

        return session('checkout_key');
    }

    /** @return Collection<int,array{product:Product,quantity:int,total_cents:int}> */
    public function lines(): Collection
    {
        $cart = $this->raw();
        $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');

        return collect($cart)->filter(fn (int $quantity, int $id): bool => $products->has($id))
            ->map(fn (int $quantity, int $id): array => ['product' => $products[$id], 'quantity' => $quantity, 'total_cents' => $products[$id]->price_cents * $quantity])->values();
    }

    public function add(Product $product, int $quantity): void
    {
        $this->set($product, ($this->raw()[$product->id] ?? 0) + $quantity);
    }

    public function set(Product $product, int $quantity): void
    {
        if (! $product->published || $quantity < 1 || $quantity > self::MAX_QUANTITY || $quantity > $product->stock) {
            throw ValidationException::withMessages(['cart' => 'Choose between 1 and '.min(self::MAX_QUANTITY, $product->stock).' available units of '.$product->name.'.']);
        }
        $cart = $this->raw();
        $cart[$product->id] = $quantity;
        session()->put('cart', $cart);
    }

    public function remove(Product $product): void
    {
        $cart = $this->raw();
        unset($cart[$product->id]);
        session()->put('cart', $cart);
    }

    public function clearAfterCheckout(string $submittedKey): void
    {
        if (hash_equals($this->checkoutKey(), $submittedKey)) {
            session()->forget('cart');
            session()->put('checkout_key', (string) Str::uuid());
        }
    }
}
