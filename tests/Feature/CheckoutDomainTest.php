<?php

namespace Tests\Feature;

use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class CheckoutDomainTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string,string> */
    private function contact(): array
    {
        return ['name' => 'Demo Shopper', 'email' => 'shopper@example.test', 'address' => '12 Example Lane', 'city' => 'Sample City', 'postal_code' => '12345'];
    }

    public function test_checkout_uses_integer_cents_and_immutable_product_snapshots(): void
    {
        $first = Product::factory()->create(['price_cents' => 1999, 'stock' => 8]);
        $second = Product::factory()->create(['price_cents' => 2550, 'stock' => 7]);
        $key = (string) Str::uuid();
        $order = app(CheckoutService::class)->place((string) Str::uuid(), $key, $key, [$first->id => 3, $second->id => 2], $this->contact());
        $this->assertSame(11097, $order->total_cents);
        $this->assertSame(2, $order->items->count());
        $this->assertSame(5, $first->refresh()->stock);
        $this->assertSame(5, $second->refresh()->stock);
        $this->assertSame('demo_confirmed', $order->status);
        $first->update(['price_cents' => 9999, 'name' => 'A later catalog name']);
        $this->assertSame(1999, $order->items()->first()->unit_price_cents);
        $this->assertSame(11097, $order->refresh()->total_cents);
        $this->assertNotSame($first->name, $order->items()->first()->product_name);
    }

    public function test_insufficient_stock_in_a_later_line_never_partially_checks_out(): void
    {
        $first = Product::factory()->create(['stock' => 8]);
        $second = Product::factory()->create(['stock' => 1]);
        $key = (string) Str::uuid();
        try {
            app(CheckoutService::class)->place((string) Str::uuid(), $key, $key, [$first->id => 2, $second->id => 2], $this->contact());
            $this->fail('Expected a stock validation error.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('stock', $exception->errors());
        }
        $this->assertSame(8, $first->refresh()->stock);
        $this->assertSame(1, $second->refresh()->stock);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
    }

    public function test_a_mid_write_failure_rolls_back_stock_and_order_rows_together(): void
    {
        $first = Product::factory()->create(['stock' => 8]);
        $second = Product::factory()->create(['stock' => 8]);
        $key = (string) Str::uuid();
        OrderItem::creating(function (OrderItem $item) use ($second): void {
            if ($item->product_id === $second->id) {
                throw new RuntimeException('Simulated second-line storage failure');
            }
        });
        try {
            app(CheckoutService::class)->place((string) Str::uuid(), $key, $key, [$first->id => 2, $second->id => 2], $this->contact());
            $this->fail('Expected a storage exception.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated second-line storage failure', $exception->getMessage());
        } finally {
            OrderItem::flushEventListeners();
        }
        $this->assertSame(8, $first->refresh()->stock);
        $this->assertSame(8, $second->refresh()->stock);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
    }

    public function test_replaying_a_completed_key_returns_the_same_order_and_decrements_stock_once(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $owner = (string) Str::uuid();
        $key = (string) Str::uuid();
        $service = app(CheckoutService::class);
        $first = $service->place($owner, $key, $key, [$product->id => 2], $this->contact());
        $second = $service->place($owner, $key, (string) Str::uuid(), [], array_replace($this->contact(), ['name' => 'A changed replay name']));
        $this->assertSame($first->id, $second->id);
        $this->assertSame('Demo Shopper', $second->name);
        $this->assertSame(3, $product->refresh()->stock);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_an_expired_key_is_rejected_without_any_writes(): void
    {
        $product = Product::factory()->create();
        try {
            app(CheckoutService::class)->place((string) Str::uuid(), (string) Str::uuid(), (string) Str::uuid(), [$product->id => 1], $this->contact());
            $this->fail('Expected an expired-key validation error.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('checkout_key', $exception->errors());
        }
        $this->assertSame(12, $product->refresh()->stock);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_a_second_shopper_cannot_buy_stock_already_taken_by_the_first(): void
    {
        $product = Product::factory()->create(['stock' => 3]);
        $firstKey = (string) Str::uuid();
        $secondKey = (string) Str::uuid();
        app(CheckoutService::class)->place((string) Str::uuid(), $firstKey, $firstKey, [$product->id => 2], $this->contact());
        try {
            app(CheckoutService::class)->place((string) Str::uuid(), $secondKey, $secondKey, [$product->id => 2], $this->contact());
            $this->fail('Expected stock contention to reject the second order.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('stock', $exception->errors());
        }
        $this->assertSame(1, $product->refresh()->stock);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_unpublished_products_and_invalid_quantities_are_rejected(): void
    {
        $product = Product::factory()->create(['published' => false]);
        $key = (string) Str::uuid();
        foreach ([[$product->id => 1], [$product->id => -1], [$product->id => '1.5']] as $cart) {
            try {
                app(CheckoutService::class)->place((string) Str::uuid(), $key, $key, $cart, $this->contact());
                $this->fail('Expected invalid basket rejection.');
            } catch (ValidationException $exception) {
                $this->assertNotEmpty($exception->errors());
            }
        }
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(12, $product->refresh()->stock);
    }
}
