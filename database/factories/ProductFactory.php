<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return ['slug' => Str::slug($name), 'name' => $name, 'category' => 'Face care', 'tagline' => 'A simple daily ritual.', 'description' => 'An original fictional personal-care product for the Haven demo shop.', 'size' => '50 ml', 'image' => 'serum', 'accent' => '#e1e8df', 'price_cents' => 1800, 'stock' => 12, 'featured' => false, 'published' => true];
    }
}
