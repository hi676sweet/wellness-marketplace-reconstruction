@props(['product'])
<article {{ $attributes->merge(['class' => 'product-card']) }} data-product-id="{{ $product->id }}" data-stock="{{ $product->stock }}">
    <a href="{{ route('products.show', $product->slug) }}" class="product-image" style="--product-background:{{ ['sand' => '#ebe4d8', 'sage' => '#e1e8df', 'peach' => '#f1dfd2', 'lavender' => '#e7e1ef'][$product->accent] ?? $product->accent }}" aria-label="View {{ $product->name }}">
        <x-product-art :image="$product->image" :name="$product->name" />
        @if($product->stock < 1)<span class="product-badge sold-out">Out of stock</span>@elseif($product->featured)<span class="product-badge">Everyday favourite</span>@endif
        <span class="image-arrow" aria-hidden="true">↗</span>
    </a>
    <div class="product-meta"><span>{{ $product->category }}</span><span>{{ $product->size }}</span></div>
    <h3><a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></h3>
    <p class="product-tagline">{{ $product->tagline }}</p>
    <div class="product-bottom"><strong>€{{ number_format($product->price_cents / 100, 2) }}</strong><form action="{{ route('basket.add') }}" method="POST">@csrf<input type="hidden" name="product_id" value="{{ $product->id }}"><input type="hidden" name="quantity" value="1"><button class="quick-add" type="submit" aria-label="Add {{ $product->name }} to basket" @disabled($product->stock < 1)><span>{{ $product->stock < 1 ? 'Sold out' : 'Add to basket' }}</span><span aria-hidden="true">+</span></button></form></div>
</article>
