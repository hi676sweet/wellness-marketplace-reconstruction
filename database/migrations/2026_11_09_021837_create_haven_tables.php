<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('category')->index();
            $table->string('tagline');
            $table->text('description');
            $table->string('size');
            $table->string('image');
            $table->string('accent')->default('sage');
            $table->unsignedInteger('price_cents');
            $table->unsignedInteger('stock');
            $table->boolean('featured')->default(false);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('reference')->unique();
            $table->uuid('owner_token')->index();
            $table->uuid('checkout_key')->unique();
            $table->string('name', 80);
            $table->string('email', 120);
            $table->string('address', 240);
            $table->string('city', 80);
            $table->string('postal_code', 20);
            $table->unsignedInteger('total_cents');
            $table->char('currency', 3)->default('EUR');
            $table->string('status')->default('demo_confirmed');
            $table->timestamps();
        });
        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->unsignedInteger('unit_price_cents');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('line_total_cents');
            $table->string('image');
            $table->string('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
    }
};
