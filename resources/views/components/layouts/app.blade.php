<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Haven is an original personal care shopping demo with fictional products and a payment-free checkout.">
    <title>{{ $title ?? 'Haven — A little care, every day' }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('haven.css') }}">
    @livewireStyles
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to content</a>
    <div class="announcement"><span>Small rituals. A little more you.</span><span>Independent demo · Fictional products</span></div>
    <header class="site-header">
        <div class="header-inner wrap">
            <a class="brand" href="{{ route('catalog') }}" aria-label="Haven home">haven<span class="brand-flower" aria-hidden="true">✳</span></a>
            <nav class="desktop-nav" aria-label="Main navigation">
                <a href="{{ route('catalog') }}#collection" @class(['active' => request()->routeIs('catalog') && !request('category')])>Shop all</a>
                <a href="{{ route('catalog', ['category' => 'Face care']) }}#collection">Face care</a>
                <a href="{{ route('catalog', ['category' => 'Body care']) }}#collection">Body care</a>
                <a href="{{ route('catalog', ['category' => 'Hair care']) }}#collection">Hair care</a>
            </nav>
            <div class="header-actions">
                <a class="orders-link" href="{{ route('orders.index') }}">Your orders</a>
                <a class="basket-link" href="{{ route('basket.show') }}" aria-label="Your basket, {{ array_sum(session('cart', [])) }} items">
                    <svg width="21" height="23" viewBox="0 0 24 25" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M5 8h14l1 15H4L5 8Z"/><path d="M8 9V6a4 4 0 0 1 8 0v3"/></svg>
                    <span class="basket-word">Basket</span><span class="basket-count">{{ array_sum(session('cart', [])) }}</span>
                </a>
                <details class="mobile-navigation"><summary aria-label="Open shop navigation"><span></span><span></span></summary><nav aria-label="Mobile navigation"><a href="{{ route('catalog') }}">Shop all</a><a href="{{ route('catalog', ['category' => 'Face care']) }}#collection">Face care</a><a href="{{ route('catalog', ['category' => 'Body care']) }}#collection">Body care</a><a href="{{ route('catalog', ['category' => 'Hair care']) }}#collection">Hair care</a><a href="{{ route('orders.index') }}">Your orders</a></nav></details>
            </div>
        </div>
    </header>
    @if(session('success'))<div class="flash wrap" role="status"><span aria-hidden="true">✓</span>{{ session('success') }}</div>@endif
    @if($errors->any())<div class="error-summary wrap" role="alert"><strong>A small thing to check</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <main id="main-content">{{ $slot }}</main>
    <footer class="site-footer"><div class="wrap footer-main"><div><a href="{{ route('catalog') }}" class="footer-brand">haven<span aria-hidden="true">✳</span></a><p>A little care, every day.<br>Find your own kind of feel-good.</p></div><div class="footer-note"><span class="eyebrow">MAKE A LITTLE ROOM FOR YOU</span><h2>Good things begin<br>with small rituals.</h2></div><nav aria-label="Footer navigation"><a href="{{ route('catalog') }}">Explore the collection ↗</a><a href="{{ route('basket.show') }}">Your basket ↗</a><a href="{{ route('orders.index') }}">Your demo orders ↗</a></nav></div><div class="wrap footer-bottom"><span>Original portfolio reconstruction. No company affiliation.</span><span>Fictional products · No real payments or deliveries</span></div></footer>
    @livewireScripts
</body>
</html>
