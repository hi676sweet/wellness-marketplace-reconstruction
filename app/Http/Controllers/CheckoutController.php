<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Services\Basket;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;

class CheckoutController extends Controller
{
    public function store(CheckoutRequest $request, Basket $basket, CheckoutService $checkout): RedirectResponse
    {
        $data = $request->validated();
        $order = $checkout->place($basket->ownerToken(), $data['checkout_key'], $basket->checkoutKey(), $basket->raw(), $data);
        $basket->clearAfterCheckout($data['checkout_key']);

        return to_route('orders.show', $order)->with('success', 'Demo order confirmed. No payment was taken.');
    }
}
