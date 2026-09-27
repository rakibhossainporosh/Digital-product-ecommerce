<?php

use App\Enums\LicenseKeyStatus;
use App\Enums\PaymentMethod;
use App\Models\Category;
use App\Models\Customer;
use App\Models\LicenseKey;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PromoCodeService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('super admin can access promo codes list page with 4 KPI cards', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    PromoCode::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get('/admin/promo-codes')
        ->assertSuccessful()
        ->assertSee('Promo Codes')
        ->assertSee('Total Promo Codes')
        ->assertSee('Total Customer Savings')
        ->assertSee('Total Redemptions')
        ->assertSee('Live Active Campaigns');
});

test('promo code service calculates percentage discount respecting max cap', function () {
    $service = app(PromoCodeService::class);
    $customer = Customer::factory()->create(['is_reseller' => false]);
    $variant = ProductVariant::factory()->create(['offer_price' => 1000.00]);

    // 20% discount but capped at 150.00 max discount
    $promo = PromoCode::factory()->percentage(20.00)->create([
        'code' => 'CAP20',
        'max_discount' => 150.00,
        'min_spend' => null,
    ]);

    $result = $service->validate('CAP20', $customer, $variant, 1);

    expect($result['valid'])->toBeTrue()
        ->and($result['subtotal'])->toBe(1000.00)
        ->and($result['discount_amount'])->toBe(150.00) // 20% of 1000 is 200, capped at 150
        ->and($result['final_total'])->toBe(850.00);
});

test('promo code service calculates fixed discount', function () {
    $service = app(PromoCodeService::class);
    $customer = Customer::factory()->create(['is_reseller' => false]);
    $variant = ProductVariant::factory()->create(['offer_price' => 500.00]);

    $promo = PromoCode::factory()->fixed(80.00)->create([
        'code' => 'FLAT80',
    ]);

    $result = $service->validate('flat80', $customer, $variant, 1); // case-insensitive

    expect($result['valid'])->toBeTrue()
        ->and($result['subtotal'])->toBe(500.00)
        ->and($result['discount_amount'])->toBe(80.00)
        ->and($result['final_total'])->toBe(420.00);
});

test('promo code service enforces minimum spend requirement', function () {
    $service = app(PromoCodeService::class);
    $customer = Customer::factory()->create(['is_reseller' => false]);
    $variant = ProductVariant::factory()->create(['offer_price' => 250.00]);

    $promo = PromoCode::factory()->fixed(50.00)->create([
        'code' => 'MIN500',
        'min_spend' => 500.00,
    ]);

    // Attempt with 1 item (subtotal 250 < 500)
    $result1 = $service->validate('MIN500', $customer, $variant, 1);
    expect($result1['valid'])->toBeFalse()
        ->and($result1['error'])->toContain('minimum order amount');

    // Attempt with 2 items (subtotal 500 >= 500)
    $result2 = $service->validate('MIN500', $customer, $variant, 2);
    expect($result2['valid'])->toBeTrue()
        ->and($result2['discount_amount'])->toBe(50.00)
        ->and($result2['final_total'])->toBe(450.00);
});

test('promo code service blocks reseller accounts when exclude_resellers is enabled', function () {
    $service = app(PromoCodeService::class);
    $reseller = Customer::factory()->reseller(10.00)->create();
    $variant = ProductVariant::factory()->create(['offer_price' => 500.00]);

    $promo = PromoCode::factory()->create([
        'code' => 'RETAILONLY',
        'exclude_resellers' => true,
    ]);

    $result = $service->validate('RETAILONLY', $reseller, $variant, 1);

    expect($result['valid'])->toBeFalse()
        ->and($result['error'])->toContain('reseller accounts');
});

test('promo code service enforces per customer usage limit', function () {
    $service = app(PromoCodeService::class);
    $customer = Customer::factory()->create(['is_reseller' => false]);
    $variant = ProductVariant::factory()->create(['offer_price' => 300.00]);

    $promo = PromoCode::factory()->create([
        'code' => 'ONCEONLY',
        'max_uses_per_customer' => 1,
    ]);

    // First check is valid
    $result1 = $service->validate('ONCEONLY', $customer, $variant, 1);
    expect($result1['valid'])->toBeTrue();

    // Create an order and simulate usage
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'promo_code_id' => $promo->id,
    ]);
    PromoCodeUsage::create([
        'promo_code_id' => $promo->id,
        'customer_id' => $customer->id,
        'order_id' => $order->id,
        'discount_amount' => 50.00,
    ]);

    // Second attempt is blocked by quota
    $result2 = $service->validate('ONCEONLY', $customer, $variant, 1);
    expect($result2['valid'])->toBeFalse()
        ->and($result2['error'])->toContain('reached the redemption limit');
});

test('promo code service enforces product and category scoping', function () {
    $service = app(PromoCodeService::class);
    $customer = Customer::factory()->create(['is_reseller' => false]);

    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    $product1 = Product::factory()->create(['category_id' => $category1->id]);
    $product2 = Product::factory()->create(['category_id' => $category2->id]);

    $variant1 = ProductVariant::factory()->create(['product_id' => $product1->id, 'offer_price' => 200.00]);
    $variant2 = ProductVariant::factory()->create(['product_id' => $product2->id, 'offer_price' => 200.00]);

    // Scoped to Product 1
    $promoProduct = PromoCode::factory()->create([
        'code' => 'PROD1ONLY',
        'product_id' => $product1->id,
    ]);

    expect($service->validate('PROD1ONLY', $customer, $variant1, 1)['valid'])->toBeTrue()
        ->and($service->validate('PROD1ONLY', $customer, $variant2, 1)['valid'])->toBeFalse();

    // Scoped to Category 1
    $promoCategory = PromoCode::factory()->create([
        'code' => 'CAT1ONLY',
        'category_id' => $category1->id,
    ]);

    expect($service->validate('CAT1ONLY', $customer, $variant1, 1)['valid'])->toBeTrue()
        ->and($service->validate('CAT1ONLY', $customer, $variant2, 1)['valid'])->toBeFalse();
});

test('promo code service rejects expired or scheduled coupons', function () {
    $service = app(PromoCodeService::class);
    $customer = Customer::factory()->create(['is_reseller' => false]);
    $variant = ProductVariant::factory()->create(['offer_price' => 200.00]);

    $expired = PromoCode::factory()->expired()->create(['code' => 'OLDCODE']);
    $scheduled = PromoCode::factory()->scheduled()->create(['code' => 'FUTURECODE']);

    expect($service->validate('OLDCODE', $customer, $variant, 1)['valid'])->toBeFalse()
        ->and($service->validate('OLDCODE', $customer, $variant, 1)['error'])->toContain('expired')
        ->and($service->validate('FUTURECODE', $customer, $variant, 1)['valid'])->toBeFalse()
        ->and($service->validate('FUTURECODE', $customer, $variant, 1)['error'])->toContain('begins on');
});

test('order checkout with promo code atomically creates usage and restitutes on refund', function () {
    $orderService = app(OrderService::class);
    $customer = Customer::factory()->withBalance(1000.00)->create(['is_reseller' => false]);
    $variant = ProductVariant::factory()->create(['regular_price' => 500.00, 'offer_price' => 500.00]);

    LicenseKey::factory()->create([
        'product_variant_id' => $variant->id,
        'status' => LicenseKeyStatus::Available,
    ]);

    $promo = PromoCode::factory()->fixed(100.00)->create([
        'code' => 'SAVE100',
        'max_uses' => 10,
        'used_count' => 0,
    ]);

    // Create order with promo code
    $order = $orderService->createOrder(
        customer: $customer,
        variant: $variant,
        quantity: 1,
        paymentMethod: PaymentMethod::Wallet,
        extra: ['promo_code' => 'SAVE100']
    );

    expect((float) $order->discount_amount)->toBe(100.00)
        ->and((float) $order->total_amount)->toBe(400.00) // 500 - 100
        ->and($order->promo_code_id)->toBe($promo->id);

    // Complete checkout
    $orderService->checkoutWithWallet($order);

    expect((float) $customer->fresh()->balance)->toBe(600.00) // 1000 - 400
        ->and($promo->fresh()->used_count)->toBe(1)
        ->and(PromoCodeUsage::where('order_id', $order->id)->count())->toBe(1);

    // Admin refund should restitute promo quota
    $admin = User::factory()->create();
    $orderService->refundOrderToWallet($order, 'Refund test', $admin);

    expect((float) $customer->fresh()->balance)->toBe(1000.00) // Full refund
        ->and($promo->fresh()->used_count)->toBe(0) // Quota restored
        ->and(PromoCodeUsage::where('order_id', $order->id)->count())->toBe(0);
});
