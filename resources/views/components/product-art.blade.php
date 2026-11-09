@props(['image' => 'serum', 'name' => 'Haven personal care product'])
<svg {{ $attributes->merge(['class' => 'product-art']) }} viewBox="0 0 320 320" role="img" aria-label="{{ $name }} packaging illustration"><use href="{{ asset('assets/products.svg') }}#{{ $image }}" /></svg>
