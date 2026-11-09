<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['reference', 'owner_token', 'checkout_key', 'name', 'email', 'address', 'city', 'postal_code', 'total_cents', 'currency', 'status'])]
#[Hidden(['owner_token', 'checkout_key'])]
class Order extends Model
{
    protected function casts(): array
    {
        return ['total_cents' => 'integer'];
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)->orderBy('id');
    }
}
