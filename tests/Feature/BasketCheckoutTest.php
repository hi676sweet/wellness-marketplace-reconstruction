<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BasketCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private string $key;

    private string $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->key = (string) Str::uuid();
        $this->owner = (string) Str::uuid();
        $this->withSession(['cart' => [], 'shopper_token' => $this->owner, 'checkout_key' => $this->key]);
        $this->app['session']->save();
        $this->withCookie(config('session.cookie'), $this->app['session']->getId());
    }

    /** @return array<string,string> */
    private function checkoutData(): array
    {
        return ['checkout_key' => $this->key, 'name' => 'Demo Shopper', 'email' => 'shopper@example.test', 'address' => '12 Example Lane', 'city' => 'Sample City', 'postal_code' => '12345'];
    }

    public function test_basket_add_update_and_remove_persist_in_the_session(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        $this->post(route('basket.add'), ['product_id' => $product->id, 'quantity' => 2])->assertSessionHasNoErrors();
        $this->post(route('basket.add'), ['product_id' => $product->id, 'quantity' => 3])->assertSessionHas('cart', [$product->id => 5]);
        $this->patch(route('basket.update', $product->id), ['quantity' => 1])->assertSessionHas('cart', [$product->id => 1]);
        $this->get(route('basket.show'))->assertOk()->assertSee($product->name);
        $this->delete(route('basket.remove', $product->id))->assertSessionHas('cart', []);
    }

    public function test_invalid_quantities_or_stock_limits_never_change_the_basket(): void
    {
        $product = Product::factory()->create(['stock' => 2]);
        foreach ([0, -1, 21, '1.5'] as $quantity) {
            $this->post(route('basket.add'), ['product_id' => $product->id, 'quantity' => $quantity])->assertSessionHasErrors('quantity');
            $this->assertSame([], session('cart'));
        }
        $this->post(route('basket.add'), ['product_id' => $product->id, 'quantity' => 3])->assertSessionHasErrors('cart');
        $this->assertSame([], session('cart'));
        $this->assertSame(2, $product->refresh()->stock);
    }

    public function test_checkout_persists_an_order_and_replay_preserves_a_new_basket(): void
    {
        $first = Product::factory()->create(['stock' => 10, 'price_cents' => 1999]);
        $next = Product::factory()->create(['stock' => 10, 'price_cents' => 4000]);
        $this->post(route('basket.add'), ['product_id' => $first->id, 'quantity' => 2]);
        $this->post(route('checkout.store'), $this->checkoutData() + ['total_cents' => 1, 'price_cents' => 1])
            ->assertSessionHasNoErrors()->assertRedirect();
        $order = Order::sole();
        $this->assertSame(3998, $order->total_cents);
        $this->assertSame(8, $first->refresh()->stock);
        $this->assertNull(session('cart'));
        $newKey = session('checkout_key');
        $this->assertNotSame($this->key, $newKey);
        $this->get(route('orders.show', $order))->assertOk()->assertSee($order->reference);
        $this->get(route('orders.index'))->assertOk()->assertSee($order->reference);
        $this->post(route('basket.add'), ['product_id' => $next->id, 'quantity' => 1])->assertSessionHasNoErrors();

        $this->post(route('checkout.store'), $this->checkoutData())->assertRedirect(route('orders.show', $order));
        $this->assertDatabaseCount('orders', 1);
        $this->assertSame([$next->id => 1], session('cart'));
        $this->assertSame($newKey, session('checkout_key'));
        $this->assertSame(10, $next->refresh()->stock);
        $this->assertSame(8, $first->refresh()->stock);
    }

    public function test_another_session_cannot_view_or_list_this_shoppers_order(): void
    {
        $product = Product::factory()->create();
        $this->post(route('basket.add'), ['product_id' => $product->id, 'quantity' => 1]);
        $this->post(route('checkout.store'), $this->checkoutData())->assertSessionHasNoErrors();
        $order = Order::sole();
        $this->withSession(['shopper_token' => (string) Str::uuid()]);
        $this->app['session']->save();
        $this->get(route('orders.show', $order))->assertNotFound();
        $this->get(route('orders.index'))->assertOk()->assertDontSee($order->reference)->assertDontSee('shopper@example.test');
    }

    public function test_stock_changes_after_basket_add_leave_a_failed_checkout_intact(): void
    {
        $product = Product::factory()->create(['stock' => 3]);
        $this->post(route('basket.add'), ['product_id' => $product->id, 'quantity' => 2]);
        $product->update(['stock' => 1]);
        $this->post(route('checkout.store'), $this->checkoutData())->assertSessionHasErrors('stock');
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame([$product->id => 2], session('cart'));
        $this->assertSame($this->key, session('checkout_key'));
        $this->assertSame(1, $product->refresh()->stock);
    }

    public function test_checkout_rejects_real_email_domains_and_an_empty_basket(): void
    {
        $this->post(route('checkout.store'), array_replace($this->checkoutData(), ['email' => 'shopper@example.com']))
            ->assertSessionHasErrors('email');
        $this->post(route('checkout.store'),$this->checkoutData())->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders',0);
    }
}
