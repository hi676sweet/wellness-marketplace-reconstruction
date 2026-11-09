<?php

namespace Tests\Feature;

use App\Livewire\Shop;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_catalog_homepage_renders_the_storefront(): void
    {
        $this->get('/')->assertOk()->assertSee('Your everyday, elevated.');
    }

    public function test_catalog_filters_combine_with_search_and_exclude_unpublished_products(): void
    {
        $match = Product::factory()->create(['name' => 'Gentle face cleanser', 'category' => 'Face care']);
        $other = Product::factory()->create(['name' => 'Gentle body wash', 'category' => 'Body care']);
        $hidden = Product::factory()->create(['name' => 'Gentle hidden cleanser', 'category' => 'Face care', 'published' => false]);
        Livewire::test(Shop::class)->set('search', 'Gentle')->set('category', 'Face care')
            ->assertSee($match->name)->assertDontSee($other->name)->assertDontSee($hidden->name);
        $this->get(route('products.show', $match->slug))->assertOk()->assertSee($match->name);
        $this->get(route('products.show', $hidden->slug))->assertNotFound();
    }

    public function test_sorting_and_clear_filters_work_with_a_paginated_catalog(): void
    {
        Product::factory()->create(['name' => 'Cheap fictional product', 'price_cents' => 900, 'category' => 'Face care']);
        Product::factory()->create(['name' => 'Costly fictional product', 'price_cents' => 9000, 'category' => 'Body care']);
        Product::factory()->count(13)->create(['price_cents' => 2000]);
        $component = Livewire::test(Shop::class)->set('sort', 'price_asc');
        $this->assertSame('Cheap fictional product', $component->instance()->products->first()->name);
        $this->assertSame(15, $component->instance()->products->total());
        $this->assertSame(12, $component->instance()->products->count());
        $component->set('sort', 'price_desc');
        $this->assertSame('Costly fictional product', $component->instance()->products->first()->name);
        $component->set('search', 'Nothing matches this')->assertSee('No essentials match');
        $component->call('resetFilters')->assertSet('search', '')->assertSet('category', '')->assertSet('sort', 'featured');
    }

    public function test_product_names_are_escaped_and_unknown_slugs_return_404(): void
    {
        $product = Product::factory()->create(['name' => '<script>alert(1)</script>']);
        $this->get(route('products.show', $product->slug))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->get('/products/a-product-that-does-not-exist')->assertNotFound();
    }
}
