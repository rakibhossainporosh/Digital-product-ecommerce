<?php

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('storefront home page renders successfully and passes products with variants for quick buy modal', function () {
    $category = Category::factory()->create(['status' => true]);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'status' => true,
    ]);

    $variant1 = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'duration_name' => '7 Days VIP',
        'duration_days' => 7,
        'regular_price' => 500.00,
        'offer_price' => 400.00,
    ]);

    $variant2 = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'duration_name' => '30 Days Pro',
        'duration_days' => 30,
        'regular_price' => 1500.00,
        'offer_price' => 1200.00,
    ]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Home', false)
            ->has('products', 1)
            ->where('products.0.id', $product->id)
            ->where('products.0.name', $product->name)
            ->where('products.0.variants_count', 2)
            ->has('products.0.variants', 2)
            ->where('products.0.variants.0.duration_name', $variant1->duration_name)
        );
});

test('customer clicking product card navigates to product details page', function () {
    $product = Product::factory()->create([
        'status' => true,
        'slug' => 'test-game-panel-pro',
    ]);

    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'regular_price' => 600.00,
    ]);

    $this->get("/product/{$product->slug}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('ProductTopup', false)
            ->where('product.id', $product->id)
            ->where('product.slug', 'test-game-panel-pro')
            ->has('product.variants', 1)
        );
});

test('reseller customer sees discounted variant prices in storefront products', function () {
    $reseller = Customer::factory()->create([
        'is_reseller' => true,
        'reseller_discount' => 10.00, // 10% discount
    ]);

    $product = Product::factory()->create(['status' => true]);
    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'regular_price' => 1000.00,
        'offer_price' => 1000.00,
    ]);

    $this->actingAs($reseller, 'customer')
        ->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Home', false)
            ->where('products.0.variants.0.effective_price', 900) // 10% off 1000 = 900
        );
});
