<?php

use App\Enums\FulfillmentStatus;
use App\Enums\LicenseKeyStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Exceptions\InsufficientWalletBalanceException;
use App\Models\Customer;
use App\Models\LicenseKey;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\OrderFulfillmentService;
use App\Services\OrderPricingService;
use App\Services\OrderService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('super admin can access orders page in admin panel with 4 KPI cards', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    Order::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get('/admin/orders')
        ->assertSuccessful()
        ->assertSee('Orders')
        ->assertSee('Total Orders')
        ->assertSee('Gross Revenue')
        ->assertSee('Pending / Processing')
        ->assertSee('Fulfilled & Delivered');
});

test('super admin can view single order detail page and relation manager table', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    $order = Order::factory()->create();
    LicenseKey::factory()->create([
        'product_variant_id' => $order->product_variant_id,
        'order_id' => $order->id,
        'status' => LicenseKeyStatus::Sold,
    ]);

    $this->actingAs($admin)
        ->get("/admin/orders/{$order->id}")
        ->assertSuccessful()
        ->assertSee($order->order_number);
});

test('pricing engine calculates standard pricing for regular customers', function () {
    $pricingService = app(OrderPricingService::class);
    $customer = Customer::factory()->create(['is_reseller' => false]);
    $variant = ProductVariant::factory()->create([
        'regular_price' => 500.00,
        'offer_price' => 400.00,
        'cost_price' => 250.00,
    ]);

    $calc = $pricingService->calculate($customer, $variant, 2);

    expect($calc['unit_price'])->toBe(400.00)
        ->and($calc['subtotal'])->toBe(800.00)
        ->and($calc['discount_amount'])->toBe(0.00)
        ->and($calc['total_amount'])->toBe(800.00)
        ->and($calc['cost_price'])->toBe(250.00)
        ->and($calc['discount_type'])->toBe('none');
});

test('pricing engine calculates tiered discount for reseller customers', function () {
    $pricingService = app(OrderPricingService::class);
    $reseller = Customer::factory()->reseller(15.00)->create();
    $variant = ProductVariant::factory()->create([
        'regular_price' => 1000.00,
        'offer_price' => 1000.00,
        'cost_price' => 600.00,
    ]);

    $calc = $pricingService->calculate($reseller, $variant, 1);

    expect($calc['unit_price'])->toBe(1000.00)
        ->and($calc['final_unit_price'])->toBe(850.00) // 15% off 1000
        ->and($calc['discount_amount'])->toBe(150.00)
        ->and($calc['total_amount'])->toBe(850.00)
        ->and($calc['discount_type'])->toBe('reseller_global_discount');
});

test('order service creates order with distinct prefix for reseller and retail', function () {
    $orderService = app(OrderService::class);
    $regular = Customer::factory()->create(['is_reseller' => false]);
    $reseller = Customer::factory()->reseller()->create();
    $variant = ProductVariant::factory()->create();

    $order1 = $orderService->createOrder($regular, $variant, 1, PaymentMethod::Wallet);
    $order2 = $orderService->createOrder($reseller, $variant, 1, PaymentMethod::Wallet);

    expect($order1->order_number)->toStartWith('ORD-')
        ->and($order2->order_number)->toStartWith('RES-')
        ->and($order1->status)->toBe(OrderStatus::Pending);
});

test('wallet checkout debits balance and atomically allocates license keys', function () {
    $orderService = app(OrderService::class);
    $customer = Customer::factory()->withBalance(1000.00)->create();
    $variant = ProductVariant::factory()->create(['regular_price' => 300.00, 'offer_price' => 300.00]);

    // Seed 2 available license keys
    $key1 = LicenseKey::factory()->create([
        'product_variant_id' => $variant->id,
        'status' => LicenseKeyStatus::Available,
    ]);
    $key2 = LicenseKey::factory()->create([
        'product_variant_id' => $variant->id,
        'status' => LicenseKeyStatus::Available,
    ]);

    $order = $orderService->createOrder($customer, $variant, 2, PaymentMethod::Wallet);
    $completedOrder = $orderService->checkoutWithWallet($order);

    expect((float) $customer->fresh()->balance)->toBe(400.00) // 1000 - 600
        ->and($completedOrder->payment_status)->toBe(PaymentStatus::Paid)
        ->and($completedOrder->fulfillment_status)->toBe(FulfillmentStatus::Fulfilled)
        ->and($completedOrder->status)->toBe(OrderStatus::Completed)
        ->and($completedOrder->fulfilled_at)->not->toBeNull()
        ->and($completedOrder->licenseKeys)->toHaveCount(2);

    // Verify keys marked as sold in DB
    expect($key1->fresh()->status)->toBe(LicenseKeyStatus::Sold)
        ->and($key1->fresh()->order_id)->toBe($order->id)
        ->and($key2->fresh()->status)->toBe(LicenseKeyStatus::Sold)
        ->and($key2->fresh()->order_id)->toBe($order->id);
});

test('wallet checkout triggers fail-safe auto-refund on stock-out', function () {
    $orderService = app(OrderService::class);
    $customer = Customer::factory()->withBalance(500.00)->create();
    $variant = ProductVariant::factory()->create(['regular_price' => 200.00, 'offer_price' => 200.00]);

    // Variant has 0 stock!
    $order = $orderService->createOrder($customer, $variant, 1, PaymentMethod::Wallet);
    $failedOrder = $orderService->checkoutWithWallet($order);

    // Balance was debited then immediately auto-refunded back to customer!
    expect((float) $customer->fresh()->balance)->toBe(500.00)
        ->and($failedOrder->fulfillment_status)->toBe(FulfillmentStatus::RefundedToWallet)
        ->and($failedOrder->status)->toBe(OrderStatus::Refunded)
        ->and($failedOrder->admin_notes)->toContain('Auto-refunded to customer wallet');
});

test('wallet checkout throws exception when customer balance is insufficient', function () {
    $orderService = app(OrderService::class);
    $customer = Customer::factory()->withBalance(50.00)->create();
    $variant = ProductVariant::factory()->create(['regular_price' => 200.00, 'offer_price' => 200.00]);

    $order = $orderService->createOrder($customer, $variant, 1, PaymentMethod::Wallet);

    expect(fn () => $orderService->checkoutWithWallet($order))
        ->toThrow(InsufficientWalletBalanceException::class);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Pending)
        ->and((float) $customer->fresh()->balance)->toBe(50.00);
});

test('admin can manually refund an order back to wallet and revoke license keys', function () {
    $orderService = app(OrderService::class);
    $admin = User::factory()->create();
    $customer = Customer::factory()->withBalance(800.00)->create();
    $variant = ProductVariant::factory()->create(['regular_price' => 400.00, 'offer_price' => 400.00]);

    $key = LicenseKey::factory()->create([
        'product_variant_id' => $variant->id,
        'status' => LicenseKeyStatus::Available,
    ]);

    $order = $orderService->createOrder($customer, $variant, 1, PaymentMethod::Wallet);
    $orderService->checkoutWithWallet($order);

    expect((float) $customer->fresh()->balance)->toBe(400.00)
        ->and($key->fresh()->status)->toBe(LicenseKeyStatus::Sold);

    // Admin refunds order
    $refundedOrder = $orderService->refundOrderToWallet($order, 'Defective code reported', $admin);

    expect((float) $customer->fresh()->balance)->toBe(800.00) // Balance refunded
        ->and($refundedOrder->status)->toBe(OrderStatus::Refunded)
        ->and($refundedOrder->payment_status)->toBe(PaymentStatus::Refunded)
        ->and($key->fresh()->status)->toBe(LicenseKeyStatus::Revoked); // Key revoked
});

test('order fulfillment service allows retrying fulfillment when stock becomes available', function () {
    $fulfillmentService = app(OrderFulfillmentService::class);
    $variant = ProductVariant::factory()->create();
    $order = Order::factory()->unfulfilled()->create([
        'product_variant_id' => $variant->id,
        'quantity' => 1,
    ]);

    // Initial attempt fails because 0 stock
    $result1 = $fulfillmentService->fulfill($order);
    expect($result1['success'])->toBeFalse()
        ->and($order->fresh()->fulfillment_status)->toBe(FulfillmentStatus::Failed);

    // Restock 1 key
    LicenseKey::factory()->create([
        'product_variant_id' => $variant->id,
        'status' => LicenseKeyStatus::Available,
    ]);

    // Retry fulfillment succeeds
    $result2 = $fulfillmentService->fulfill($order->fresh());
    expect($result2['success'])->toBeTrue()
        ->and($order->fresh()->fulfillment_status)->toBe(FulfillmentStatus::Fulfilled)
        ->and($order->fresh()->status)->toBe(OrderStatus::Completed);
});

test('service order checkout with wallet keeps order in processing status without allocating keys', function () {
    $orderService = app(OrderService::class);
    $customer = Customer::factory()->create(['balance' => 500.00]);
    $serviceProduct = Product::factory()->create(['type' => 'service']);
    $variant = ProductVariant::factory()->create([
        'product_id' => $serviceProduct->id,
        'price' => 150.00,
    ]);

    $order = $orderService->createOrder($customer, $variant, 1, PaymentMethod::Wallet);
    $order->update([
        'service_data' => [
            'device_model' => 'Samsung Galaxy S23',
            'imei' => '123456789012345',
            'whatsapp_number' => '+8801700000000',
        ],
    ]);

    $orderService->checkoutWithWallet($order);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Paid)
        ->and($order->fresh()->status)->toBe(OrderStatus::Processing)
        ->and($order->fresh()->fulfillment_status)->toBe(FulfillmentStatus::Unfulfilled)
        ->and($order->fresh()->licenseKeys)->toBeEmpty()
        ->and((float) $customer->fresh()->balance)->toBe(350.00);
});

test('admin can manually fulfill service order via fulfillServiceOrder with notes', function () {
    $fulfillmentService = app(OrderFulfillmentService::class);
    $serviceProduct = Product::factory()->create(['type' => 'service']);
    $variant = ProductVariant::factory()->create([
        'product_id' => $serviceProduct->id,
    ]);

    $order = Order::factory()->create([
        'product_id' => $serviceProduct->id,
        'product_variant_id' => $variant->id,
        'payment_status' => PaymentStatus::Paid,
        'status' => OrderStatus::Processing,
        'fulfillment_status' => FulfillmentStatus::Unfulfilled,
    ]);

    $result = $fulfillmentService->fulfillServiceOrder($order, 'Rooted via Magisk v27.0');

    expect($result['success'])->toBeTrue()
        ->and($result['source'])->toBe('manual_service')
        ->and($order->fresh()->fulfillment_status)->toBe(FulfillmentStatus::Fulfilled)
        ->and($order->fresh()->status)->toBe(OrderStatus::Completed)
        ->and($order->fresh()->fulfilled_at)->not->toBeNull()
        ->and($order->fresh()->admin_notes)->toContain('Rooted via Magisk v27.0');
});

test('fulfillServiceOrder fails when order is not paid', function () {
    $fulfillmentService = app(OrderFulfillmentService::class);
    $serviceProduct = Product::factory()->create(['type' => 'service']);
    $variant = ProductVariant::factory()->create([
        'product_id' => $serviceProduct->id,
    ]);

    $order = Order::factory()->create([
        'product_id' => $serviceProduct->id,
        'product_variant_id' => $variant->id,
        'payment_status' => PaymentStatus::Pending,
        'status' => OrderStatus::Pending,
        'fulfillment_status' => FulfillmentStatus::Unfulfilled,
    ]);

    $result = $fulfillmentService->fulfillServiceOrder($order);

    expect($result['success'])->toBeFalse()
        ->and($result['error'])->toBe('Cannot fulfill an unpaid service order.')
        ->and($order->fresh()->fulfillment_status)->toBe(FulfillmentStatus::Unfulfilled);
});
