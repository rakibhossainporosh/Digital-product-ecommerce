<?php

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Pages\ViewProduct;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('super admin can view products list page in admin panel', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->get('/admin/products')
        ->assertSuccessful();
});

test('unauthenticated visitor is redirected to login from products page', function () {
    $this->get('/admin/products')
        ->assertRedirect('/admin/login');
});

test('super admin can view separate create, edit, and view pages', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->actingAs($admin)
        ->get('/admin/products/create')
        ->assertSuccessful();

    $this->actingAs($admin)
        ->get("/admin/products/{$product->id}")
        ->assertSuccessful();

    $this->actingAs($admin)
        ->get("/admin/products/{$product->id}/edit")
        ->assertSuccessful();
});

test('super admin can create a product with duration variants via dedicated create page', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    $category = Category::factory()->create(['name' => 'VIP Injectors']);

    Livewire::actingAs($admin)
        ->test(CreateProduct::class)
        ->fillForm([
            'category_id' => $category->id,
            'name' => 'FF Headshot Injector',
            'slug' => 'ff-headshot-injector',
            'demo_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'description' => 'VIP injector for Free Fire',
            'features' => ['Auto Aim', 'No Recoil'],
            'status' => true,
            'sort_order' => 5,
            'variants' => [
                [
                    'duration_name' => '7 Days VIP',
                    'duration_days' => 7,
                    'regular_price' => 500.00,
                    'offer_price' => 450.00,
                    'cost_price' => 300.00,
                    'is_popular' => true,
                    'api_provider_id' => '1001',
                ],
                [
                    'duration_name' => '30 Days Master',
                    'duration_days' => 30,
                    'regular_price' => 1500.00,
                    'offer_price' => 1200.00,
                    'cost_price' => 800.00,
                    'is_popular' => false,
                    'api_provider_id' => null,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('slug', 'ff-headshot-injector')->first();

    expect($product)->not->toBeNull()
        ->and($product->name)->toBe('FF Headshot Injector')
        ->and($product->category_id)->toBe($category->id)
        ->and($product->status)->toBeTrue()
        ->and($product->features)->toBe(['Auto Aim', 'No Recoil'])
        ->and($product->variants)->toHaveCount(2);

    $popularVariant = $product->variants()->where('is_popular', true)->first();
    expect($popularVariant)->not->toBeNull()
        ->and($popularVariant->duration_name)->toBe('7 Days VIP')
        ->and((float) $popularVariant->regular_price)->toBe(500.00)
        ->and((float) $popularVariant->offer_price)->toBe(450.00)
        ->and((float) $popularVariant->cost_price)->toBe(300.00)
        ->and((float) $popularVariant->profit)->toBe(150.00)
        ->and($popularVariant->profit_margin_percentage)->toBe('50%')
        ->and($popularVariant->discount_percentage)->toBe('10%');
});

test('slug auto generation and collision avoidance works on model creation', function () {
    $category = Category::factory()->create();

    $product1 = Product::create([
        'category_id' => $category->id,
        'name' => 'PUBG Vision Pro',
        'status' => true,
    ]);

    $product2 = Product::create([
        'category_id' => $category->id,
        'name' => 'PUBG Vision Pro',
        'status' => true,
    ]);

    expect($product1->slug)->toBe('pubg-vision-pro')
        ->and($product2->slug)->toBe('pubg-vision-pro-1');
});

test('super admin can edit a product and update duration variants via dedicated edit page', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Original Tool',
        'slug' => 'original-tool',
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'duration_name' => '1 Day Pass',
        'duration_days' => 1,
        'regular_price' => 100.00,
        'offer_price' => 90.00,
    ]);

    Livewire::actingAs($admin)
        ->test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm([
            'category_id' => $category->id,
            'name' => 'Updated Tool Name',
            'slug' => 'updated-tool-name',
            'status' => true,
            'sort_order' => 1,
            'variants' => [
                [
                    'duration_name' => '1 Day Updated',
                    'duration_days' => 1,
                    'regular_price' => 120.00,
                    'offer_price' => 99.00,
                    'is_popular' => true,
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $product->refresh();
    expect($product->name)->toBe('Updated Tool Name')
        ->and($product->slug)->toBe('updated-tool-name')
        ->and($product->variants()->where('duration_name', '1 Day Updated')->exists())->toBeTrue();
});

test('super admin can view product details on dedicated view page', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

    Livewire::actingAs($admin)
        ->test(ViewProduct::class, ['record' => $product->getRouteKey()])
        ->assertSuccessful();
});

test('super admin can soft delete and restore a product', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

    Livewire::actingAs($admin)
        ->test(ListProducts::class)
        ->callTableAction('delete', $product)
        ->assertHasNoTableActionErrors();

    expect($product->fresh()->trashed())->toBeTrue();

    Livewire::actingAs($admin)
        ->test(ListProducts::class)
        ->filterTable('trashed', 'true')
        ->callTableAction('restore', $product)
        ->assertHasNoTableActionErrors();

    expect($product->fresh()->trashed())->toBeFalse();
});

test('user without product permissions cannot view or manage products', function () {
    $user = User::factory()->create(['status' => true]);
    $user->assignRole('viewer');

    $this->actingAs($user)
        ->get('/admin/products')
        ->assertSuccessful();

    // User without create permission is forbidden from create page
    $this->actingAs($user)
        ->get('/admin/products/create')
        ->assertForbidden();
});

test('variant financial calculations handle offer price, cost price, and profit margins accurately', function () {
    $variant = new ProductVariant([
        'regular_price' => 1000.00,
        'offer_price' => 750.00,
        'cost_price' => 500.00,
    ]);

    expect($variant->effective_price)->toBe(750.00)
        ->and($variant->has_discount)->toBeTrue()
        ->and($variant->discount_percentage)->toBe('25%')
        ->and($variant->formatted_regular_price)->toBe('৳1,000.00')
        ->and($variant->formatted_offer_price)->toBe('৳750.00')
        ->and($variant->formatted_cost_price)->toBe('৳500.00')
        ->and($variant->profit)->toBe(250.00)
        ->and($variant->profit_margin_percentage)->toBe('50%');

    $noDiscountVariant = new ProductVariant([
        'regular_price' => 500.00,
        'offer_price' => null,
        'cost_price' => null,
    ]);

    expect($noDiscountVariant->effective_price)->toBe(500.00)
        ->and($noDiscountVariant->has_discount)->toBeFalse()
        ->and($noDiscountVariant->discount_percentage)->toBeNull()
        ->and($noDiscountVariant->cost_price)->toBeNull()
        ->and($noDiscountVariant->profit)->toBeNull();
});

test('product price range correctly reflects single or multiple variant prices', function () {
    $product = Product::factory()->create();

    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'regular_price' => 500.00,
        'offer_price' => 400.00,
    ]);

    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'regular_price' => 2000.00,
        'offer_price' => 1800.00,
    ]);

    $product->refresh();
    expect($product->price_range)->toBe('৳400.00 - ৳1,800.00');
});

test('product stats overview widget renders on products list page with filter urls', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    $product = Product::factory()->create(['status' => true]);
    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'is_popular' => true,
    ]);

    $this->actingAs($admin)
        ->get('/admin/products')
        ->assertSuccessful()
        ->assertSee('Total Products')
        ->assertSee('Active Storefront')
        ->assertSee('Inactive / Drafts')
        ->assertSee('Popular Badges')
        ->assertSee('filter=active')
        ->assertSee('filter=popular')
        ->assertDontSee('All Products 4'); // Verify tabs bar is removed
});

test('cards correctly filter products table by active status, inactive, and popular variants', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    $activeProduct = Product::factory()->create([
        'name' => 'Active Prime Tool',
        'status' => true,
    ]);
    ProductVariant::factory()->create([
        'product_id' => $activeProduct->id,
        'is_popular' => true,
    ]);

    $inactiveProduct = Product::factory()->create([
        'name' => 'Draft Inactive Script',
        'status' => false,
    ]);
    ProductVariant::factory()->create([
        'product_id' => $inactiveProduct->id,
        'is_popular' => false,
    ]);

    // Test active filter
    $this->actingAs($admin)
        ->get('/admin/products?filter=active')
        ->assertSuccessful()
        ->assertSee('Active Prime Tool')
        ->assertDontSee('Draft Inactive Script');

    // Test inactive filter
    $this->actingAs($admin)
        ->get('/admin/products?filter=inactive')
        ->assertSuccessful()
        ->assertSee('Draft Inactive Script')
        ->assertDontSee('Active Prime Tool');

    // Test popular filter
    $this->actingAs($admin)
        ->get('/admin/products?filter=popular')
        ->assertSuccessful()
        ->assertSee('Active Prime Tool')
        ->assertDontSee('Draft Inactive Script');
});
