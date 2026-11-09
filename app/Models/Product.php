<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slug', 'name', 'category', 'tagline', 'description', 'size', 'image', 'accent', 'price_cents', 'stock', 'featured', 'published'])]
class Product extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['price_cents' => 'integer', 'stock' => 'integer', 'featured' => 'boolean', 'published' => 'boolean'];
    }

    public function formattedPrice(): string
    {
        return "\u{20AC}".number_format($this->price_cents / 100, 2);
    }
}
