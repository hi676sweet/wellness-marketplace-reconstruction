<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    /** @param array<int,int> $cart @param array<string,mixed> $contact */
    public function place(string $ownerToken, string $submittedKey, string $currentKey, array $cart, array $contact): Order
    {
        $existing = $this->previous($ownerToken, $submittedKey);
        if ($existing) {
            return $existing;
        }
        if (! hash_equals($currentKey, $submittedKey)) {
            throw ValidationException::withMessages(['checkout_key' => 'This checkout link has expired. Reload your basket and try again.']);
        }
        if ($cart === []) {
            throw ValidationException::withMessages(['cart' => 'Your basket is empty. Add a product before checking out.']);
        }
        foreach ($cart as $id => $quantity) {
            if (! is_numeric($id) || ! is_int($quantity) || $quantity < 1 || $quantity > Basket::MAX_QUANTITY) {
                throw ValidationException::withMessages(['cart' => 'The basket contains an invalid quantity. Update it before checkout.']);
            }
        }

        try {
            return DB::transaction(function () use ($ownerToken, $submittedKey, $cart, $contact): Order {
                $existing = $this->previous($ownerToken, $submittedKey);
                if ($existing) {
                    return $existing;
                }
                $products = Product::whereIn('id', array_keys($cart))->orderBy('id')->get()->keyBy('id');
                if ($products->count() !== count($cart)) {
                    throw ValidationException::withMessages(['cart' => 'A product is no longer available. Remove it from the basket and try again.']);
                }
                $total = 0;
                foreach ($cart as $id => $quantity) {
                    $product = $products[$id];
                    if (! $product->published || $product->stock < $quantity) {
                        throw ValidationException::withMessages(['stock' => 'There is not enough stock of '.$product->name.'. Update your basket before trying again.']);
                    }
                    $total += $product->price_cents * $quantity;
                }

                $order = Order::create(Arr::only($contact, ['name', 'email', 'address', 'city', 'postal_code']) + [
                    'reference' => 'HAV-'.Str::upper(Str::random(10)), 'owner_token' => $ownerToken, 'checkout_key' => $submittedKey,
                    'total_cents' => $total, 'currency' => 'EUR', 'status' => 'demo_confirmed',
                ]);
                foreach ($products as $product) {
                    $quantity = $cart[$product->id];
                    $changed = Product::whereKey($product->id)->where('published', true)->where('stock', '>=', $quantity)->decrement('stock', $quantity);
                    if ($changed !== 1) {
                        throw ValidationException::withMessages(['stock' => 'Stock changed while checking out. Your order was not placed; please review the basket.']);
                    }
                    $order->items()->create([
                        'product_id' => $product->id, 'product_name' => $product->name, 'unit_price_cents' => $product->price_cents,
                        'quantity' => $quantity, 'line_total_cents' => $product->price_cents * $quantity, 'image' => $product->image, 'size' => $product->size,
                    ]);
                }

                return $order->load('items');
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            return $this->previous($ownerToken, $submittedKey) ?? throw $exception;
        }
    }

    private function previous(string $ownerToken, string $key): ?Order
    {
        return Order::with('items')->where('owner_token', $ownerToken)->where('checkout_key',$key)->first();
    }
}
