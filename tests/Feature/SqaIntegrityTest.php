<?php

use App\Enums\DiscountType;
use App\Enums\FulfillmentStatus;
use App\Enums\LicenseKeyStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\WalletTransactionType;
use App\Exceptions\CustomerAccountLockedException;
use App\Models\Customer;
use App\Models\LicenseKey;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\PromoCode;
use App\Models\User;
use App\Services\OrderFulfillmentService;
use App\Services\OrderService;
use App\Services\PromoCodeService;
use App\Services\WalletService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use RuntimeException;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(SettingSeeder::class);
});

/*
|--------------------------------------------------------------------------
| DOMAIN 1: WALLET FINANCIAL INTEGRITY & BOUNDARY TESTING
|--------------------------------------------------------------------------
*/

test('wallet service strictly rejects zero or negative amounts for credit and debit', function (): void {
    $walletService = app(WalletService::class);
    $customer = Customer::factory()->withBalance(500.00)->create();

    // Reject zero credit
    expect(fn () => $walletService->credit(
        customer: $customer,
        amount: 0.00,
        type: WalletTransactionType::Deposit,
        description: 'Zero credit test'
    ))->toThrow(InvalidArgumentException::class);

    // Reject negative credit
    expect(fn () => $walletService->credit(
        customer: $customer,
        amount: -100.00,
        type: WalletTransactionType::Deposit,
        description: 'Negative credit test'
    ))->toThrow(InvalidArgumentException::class);

    // Reject zero debit
    expect(fn () => $walletService->debit(
        customer: $customer,
        amount: 0.00,
        type: WalletTransactionType::Purchase,
        description: 'Zero debit test'
    ))->toThrow(InvalidArgumentException::class);

    // Reject negative debit
    expect(fn () => $walletService->debit(
        customer: $customer,
        amount: -50.00,
        type: WalletTransactionType::Purchase,
        description: 'Negative debit test'
    ))->toThrow(InvalidArgumentException::class);

    // Balance remains untouched
    expect((float) $customer->fresh()->balance)->toBe(500.00);
});

test('wallet service blocks credit and debit on banned customer accounts', function (): void {
    $walletService = app(WalletService::class);
    $bannedCustomer = Customer::factory()->banned()->withBalance(200.00)->create();

    expect(fn () => $walletService->credit(
        customer: $bannedCustomer,
        amount: 100.00,
        type: WalletTransactionType::Deposit,
        description: 'Deposit to banned user'
    ))->toThrow(CustomerAccountLockedException::class);

    expect(fn () => $walletService->debit(
        customer: $bannedCustomer,
        amount: 50.00,
        type: WalletTransactionType::Purchase,
        description: 'Debit from banned user'
    ))->toThrow(CustomerAccountLockedException::class);

    expect((float) $bannedCustomer->fresh()->balance)->toBe(200.00);
});

test('exact balance debit exhausts wallet to precisely 0.00 without rounding defects', function (): void {
    $walletService = app(WalletService::class);
    $customer = Customer::factory()->withBalance(149.99)->create();

    $walletService->debit(
        customer: $customer,
        amount: 149.99,
        type: WalletTransactionType::Purchase,
        description: 'Exact balance checkout'
    );

    expect((float) $customer->fresh()->balance)->toBe(0.00);
});

/*
|--------------------------------------------------------------------------
| DOMAIN 2: INVENTORY ATOMICITY & ROLLBACK STRESS TESTING
|--------------------------------------------------------------------------
*/

test('partial stock availability triggers atomic rollback and full auto-refund', function (): void {
    $orderService = app(OrderService::class);
    $customer = Customer::factory()->withBalance(1500.00)->create();
    $variant = ProductVariant::factory()->create([
        'regular_price' => 500.00,
        'offer_price' => 500.00,
    ]);

    // Order demands 3 keys, but ONLY 2 keys exist in stock
    $key1 = LicenseKey::factory()->create([
        'product_variant_id' => $variant->id,
        'status' => LicenseKeyStatus::Available,
    ]);
    $key2 = LicenseKey::factory()->create([
        'product_variant_id' => $variant->id,
        'status' => LicenseKeyStatus::Available,
    ]);

    $order = $orderService->createOrder($customer, $variant, 3, PaymentMethod::Wallet);
    $processedOrder = $orderService->checkoutWithWallet($order);

    // 1. Order status must be Refunded due to incomplete delivery
    expect($processedOrder->status)->toBe(OrderStatus::Refunded)
        ->and($processedOrder->fulfillment_status)->toBe(FulfillmentStatus::RefundedToWallet);

    // 2. Both keys must be released back to Available and order_id detached!
    expect($key1->fresh()->status)->toBe(LicenseKeyStatus::Available)
        ->and($key1->fresh()->order_id)->toBeNull();
    expect($key2->fresh()->status)->toBe(LicenseKeyStatus::Available)
        ->and($key2->fresh()->order_id)->toBeNull();

    // 3. Customer wallet must be refunded full ৳1500.00!
    expect((float) $customer->fresh()->balance)->toBe(1500.00);
});

test('revoked license keys cannot be allocated to new customer orders', function (): void {
    $orderService = app(OrderService::class);
    $customer = Customer::factory()->withBalance(1000.00)->create();
    $variant = ProductVariant::factory()->create(['regular_price' => 300.00, 'offer_price' => 300.00]);

    // Seed 1 revoked key and 0 available keys
    $revokedKey = LicenseKey::factory()->create([
        'product_variant_id' => $variant->id,
        'status' => LicenseKeyStatus::Revoked,
    ]);

    $order = $orderService->createOrder($customer, $variant, 1, PaymentMethod::Wallet);
    $processedOrder = $orderService->checkoutWithWallet($order);

    // Order must fail fulfillment because revoked keys cannot be re-sold
    expect($processedOrder->fulfillment_status)->toBe(FulfillmentStatus::RefundedToWallet)
        ->and($revokedKey->fresh()->status)->toBe(LicenseKeyStatus::Revoked)
        ->and($revokedKey->fresh()->order_id)->toBeNull();
});

/*
|--------------------------------------------------------------------------
| DOMAIN 3: ORDER LIFECYCLE & ILLEGAL STATE TRANSITION GUARDS
|--------------------------------------------------------------------------
*/

test('order service forbids refunding an unpaid order', function (): void {
    $orderService = app(OrderService::class);
    $admin = User::factory()->create();
    $customer = Customer::factory()->create();
    $variant = ProductVariant::factory()->create();

    $order = $orderService->createOrder($customer, $variant, 1, PaymentMethod::Gateway);

    // Order is pending payment (isPaid = false)
    expect($order->isPaid())->toBeFalse()
        ->and($order->canBeRefunded())->toBeFalse();

    expect(fn () => $orderService->refundOrderToWallet($order, 'Accidental refund attempt', $admin))
        ->toThrow(RuntimeException::class);
});

test('order service forbids double-refunding an already refunded order', function (): void {
    $orderService = app(OrderService::class);
    $admin = User::factory()->create();
    $customer = Customer::factory()->withBalance(600.00)->create();
    $variant = ProductVariant::factory()->create(['regular_price' => 300.00, 'offer_price' => 300.00]);
    LicenseKey::factory()->create(['product_variant_id' => $variant->id, 'status' => LicenseKeyStatus::Available]);

    $order = $orderService->createOrder($customer, $variant, 1, PaymentMethod::Wallet);
    $orderService->checkoutWithWallet($order);

    // First refund succeeds
    $orderService->refundOrderToWallet($order, 'Legitimate customer refund', $admin);
    expect($order->fresh()->status)->toBe(OrderStatus::Refunded);

    // Second refund MUST be blocked!
    expect($order->fresh()->canBeRefunded())->toBeFalse();
    expect(fn () => $orderService->refundOrderToWallet($order->fresh(), 'Malicious double refund', $admin))
        ->toThrow(RuntimeException::class);

    // Customer balance must only receive 1 refund
    expect((float) $customer->fresh()->balance)->toBe(600.00);
});

test('order fulfillment service refuses to fulfill cancelled or refunded orders', function (): void {
    $fulfillmentService = app(OrderFulfillmentService::class);
    $customer = Customer::factory()->create();
    $variant = ProductVariant::factory()->create();
    LicenseKey::factory()->count(2)->create(['product_variant_id' => $variant->id, 'status' => LicenseKeyStatus::Available]);

    $cancelledOrder = Order::factory()->create([
        'customer_id' => $customer->id,
        'product_variant_id' => $variant->id,
        'quantity' => 1,
        'status' => OrderStatus::Cancelled,
        'fulfillment_status' => FulfillmentStatus::Unfulfilled,
    ]);

    $refundedOrder = Order::factory()->create([
        'customer_id' => $customer->id,
        'product_variant_id' => $variant->id,
        'quantity' => 1,
        'status' => OrderStatus::Refunded,
        'fulfillment_status' => FulfillmentStatus::Unfulfilled,
    ]);

    $res1 = $fulfillmentService->fulfill($cancelledOrder);
    $res2 = $fulfillmentService->fulfill($refundedOrder);

    expect($res1['success'])->toBeFalse()
        ->and($res1['error'])->toContain('Cannot fulfill')
        ->and($res2['success'])->toBeFalse()
        ->and($res2['error'])->toContain('Cannot fulfill');
});

/*
|--------------------------------------------------------------------------
| DOMAIN 4: PROMO CODE SECURITY & QUOTA RECOVERY TESTING
|--------------------------------------------------------------------------
*/

test('promo code engine enforces minimum spend constraint', function (): void {
    $promoService = app(PromoCodeService::class);
    $customer = Customer::factory()->create();
    $variant = ProductVariant::factory()->create(['regular_price' => 300.00, 'offer_price' => 300.00]);

    $promo = PromoCode::factory()->create([
        'code' => 'MIN500',
        'type' => DiscountType::Fixed,
        'value' => 50.00,
        'min_spend' => 500.00,
        'is_active' => true,
    ]);

    // Quantity 1 = ৳300.00 (< ৳500 minimum spend) -> Invalid
    $resultBelow = $promoService->validate('MIN500', $customer, $variant, 1);
    expect($resultBelow['valid'])->toBeFalse()
        ->and($resultBelow['error'])->toContain('minimum order amount');

    // Quantity 2 = ৳600.00 (>= ৳500 minimum spend) -> Valid
    $resultAbove = $promoService->validate('MIN500', $customer, $variant, 2);
    expect($resultAbove['valid'])->toBeTrue()
        ->and($resultAbove['discount_amount'])->toBe(50.00);
});

test('order refund restores promo code usage quota for customer reuse', function (): void {
    $orderService = app(OrderService::class);
    $admin = User::factory()->create();
    $customer = Customer::factory()->withBalance(1000.00)->create();
    $variant = ProductVariant::factory()->create(['regular_price' => 400.00, 'offer_price' => 400.00]);
    LicenseKey::factory()->create(['product_variant_id' => $variant->id, 'status' => LicenseKeyStatus::Available]);

    $promo = PromoCode::factory()->create([
        'code' => 'ONCEONLY',
        'type' => DiscountType::Fixed,
        'value' => 50.00,
        'max_uses_per_customer' => 1,
        'is_active' => true,
    ]);

    // 1. Place order with coupon
    $order = $orderService->createOrder($customer, $variant, 1, PaymentMethod::Wallet, ['promo_code' => 'ONCEONLY']);
    $orderService->checkoutWithWallet($order);

    // 2. Coupon is now consumed (cannot use on 2nd order)
    $promoService = app(PromoCodeService::class);
    $checkConsumed = $promoService->validate('ONCEONLY', $customer, $variant, 1);
    expect($checkConsumed['valid'])->toBeFalse()
        ->and($checkConsumed['error'])->toContain('redemption limit');

    // 3. Admin refunds order
    $orderService->refundOrderToWallet($order, 'Customer requested cancellation', $admin);

    // 4. Quota is restored! Customer can now validly reuse the promo code
    $checkRestored = $promoService->validate('ONCEONLY', $customer, $variant, 1);
    expect($checkRestored['valid'])->toBeTrue()
        ->and($checkRestored['discount_amount'])->toBe(50.00);
});

/*
|--------------------------------------------------------------------------
| DOMAIN 5: ACCESS CONTROL & IDOR SECURITY TESTING
|--------------------------------------------------------------------------
*/

test('banned customer cannot create orders or checkout', function (): void {
    $orderService = app(OrderService::class);
    $banned = Customer::factory()->banned()->withBalance(500.00)->create();
    $variant = ProductVariant::factory()->create();

    expect(fn () => $orderService->createOrder($banned, $variant, 1, PaymentMethod::Wallet))
        ->toThrow(CustomerAccountLockedException::class);
});

test('customer a cannot view customer b orders or license keys via portal', function (): void {
    $customerA = Customer::factory()->create();
    $customerB = Customer::factory()->create();

    $variant = ProductVariant::factory()->create();

    // Customer B has a private order and key
    $orderB = Order::factory()->create([
        'customer_id' => $customerB->id,
        'product_variant_id' => $variant->id,
    ]);

    $keyB = LicenseKey::factory()->create([
        'product_variant_id' => $variant->id,
        'order_id' => $orderB->id,
        'key' => 'SECRET-KEY-FOR-CUSTOMER-B',
        'status' => LicenseKeyStatus::Sold,
    ]);

    // Customer A logs in and accesses My Keys
    $this->actingAs($customerA, 'customer')
        ->get('/keys')
        ->assertSuccessful()
        ->assertDontSee('SECRET-KEY-FOR-CUSTOMER-B');

    // Customer A accesses Orders page
    $this->actingAs($customerA, 'customer')
        ->get('/orders')
        ->assertSuccessful()
        ->assertDontSee($orderB->order_number);
});

/*
|--------------------------------------------------------------------------
| DOMAIN 6: INPUT BOUNDARY & TAMPER RESISTANCE
|--------------------------------------------------------------------------
*/

test('checkout controller strictly validates quantity limits between 1 and 50', function (): void {
    $customer = Customer::factory()->withBalance(10000.00)->create();
    $variant = ProductVariant::factory()->create();

    // Attempt quantity = 0
    $responseZero = $this->actingAs($customer, 'customer')
        ->post('/checkout/wallet', [
            'variant_id' => $variant->id,
            'quantity' => 0,
        ]);
    $responseZero->assertSessionHasErrors('quantity');

    // Attempt negative quantity
    $responseNegative = $this->actingAs($customer, 'customer')
        ->post('/checkout/wallet', [
            'variant_id' => $variant->id,
            'quantity' => -5,
        ]);
    $responseNegative->assertSessionHasErrors('quantity');

    // Attempt excessive quantity (> 50)
    $responseExcess = $this->actingAs($customer, 'customer')
        ->post('/checkout/wallet', [
            'variant_id' => $variant->id,
            'quantity' => 9999,
        ]);
    $responseExcess->assertSessionHasErrors('quantity');
});
