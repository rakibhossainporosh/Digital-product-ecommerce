<?php

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentTransactionStatus;
use App\Enums\PaymentTransactionType;
use App\Filament\Resources\PaymentTransactions\PaymentTransactionResource;
use App\Models\Customer;
use App\Models\LicenseKey;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Setting::updateOrCreate(['key' => 'uddoktapay_api_key'], ['value' => 'test-api-key', 'type' => 'string']);
    Setting::updateOrCreate(['key' => 'uddoktapay_base_url'], ['value' => 'https://sandbox.uddoktapay.com', 'type' => 'string']);

    $this->service = app(PaymentGatewayService::class);
    $this->customer = Customer::factory()->create(['balance' => 0]);
});

it('can initiate a deposit session', function () {
    $transaction = $this->service->initiateDeposit($this->customer, 500);

    expect($transaction)->toBeInstanceOf(PaymentTransaction::class)
        ->type->toBe(PaymentTransactionType::Deposit)
        ->amount->toEqual(500)
        ->status->toBe(PaymentTransactionStatus::Pending)
        ->customer_id->toBe($this->customer->id);
});

it('can initiate an order payment session', function () {
    $order = Order::factory()->create(['customer_id' => $this->customer->id, 'status' => OrderStatus::Pending, 'total_amount' => 1250]);
    $transaction = $this->service->initiateOrderPayment($order);

    expect($transaction)->toBeInstanceOf(PaymentTransaction::class)
        ->type->toBe(PaymentTransactionType::OrderPayment)
        ->amount->toEqual(1250)
        ->order_id->toBe($order->id)
        ->status->toBe(PaymentTransactionStatus::Pending);
});

it('rejects webhook with invalid api key', function () {
    $response = $this->postJson('/api/payment/webhook', [], [
        'RT-UDDOKTAPAY-API-KEY' => 'wrong-key',
    ]);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthorized Access']);
});

it('processes webhook idempotently', function () {
    $transaction = PaymentTransaction::factory()->create([
        'customer_id' => $this->customer->id,
        'type' => PaymentTransactionType::Deposit,
        'status' => PaymentTransactionStatus::Pending,
        'amount' => 500,
    ]);

    $payload = [
        'status' => 'COMPLETED',
        'invoice_id' => 'INV123',
        'metadata' => [
            'transaction_uuid' => $transaction->uuid,
        ],
    ];

    $response1 = $this->postJson('/api/payment/webhook', $payload, [
        'RT-UDDOKTAPAY-API-KEY' => 'test-api-key',
    ]);

    $response1->assertStatus(200);
    expect($this->customer->refresh()->balance)->toEqual(500);
    expect($transaction->refresh()->status)->toBe(PaymentTransactionStatus::Completed);

    // Send again
    $response2 = $this->postJson('/api/payment/webhook', $payload, [
        'RT-UDDOKTAPAY-API-KEY' => 'test-api-key',
    ]);

    $response2->assertStatus(200);
    // Balance shouldn't increase again
    expect($this->customer->refresh()->balance)->toEqual(500);
});

it('fulfills order on successful payment', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
    LicenseKey::factory()->create(['product_variant_id' => $variant->id]); // 1 in stock

    $order = Order::factory()->pending()->create([
        'customer_id' => $this->customer->id,
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'quantity' => 1,
    ]);

    $transaction = PaymentTransaction::factory()->create([
        'customer_id' => $this->customer->id,
        'order_id' => $order->id,
        'type' => PaymentTransactionType::OrderPayment,
        'status' => PaymentTransactionStatus::Pending,
        'amount' => $order->total_amount,
    ]);

    $payload = [
        'status' => 'COMPLETED',
        'invoice_id' => 'INV123',
        'metadata' => [
            'transaction_uuid' => $transaction->uuid,
        ],
    ];

    $response = $this->postJson('/api/payment/webhook', $payload, [
        'RT-UDDOKTAPAY-API-KEY' => 'test-api-key',
    ]);

    $response->assertStatus(200);

    expect($order->refresh()->payment_status)->toBe(PaymentStatus::Paid);

    if ($order->status !== OrderStatus::Completed) {
        dump($order->admin_notes);
    }

    expect($order->status)->toBe(OrderStatus::Completed)
        ->and($order->fulfillment_status)->toBe(FulfillmentStatus::Fulfilled);
});

it('auto-refunds to wallet on stockout', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
    // 0 in stock

    $order = Order::factory()->pending()->create([
        'customer_id' => $this->customer->id,
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'quantity' => 1,
    ]);

    $transaction = PaymentTransaction::factory()->create([
        'customer_id' => $this->customer->id,
        'order_id' => $order->id,
        'type' => PaymentTransactionType::OrderPayment,
        'status' => PaymentTransactionStatus::Pending,
        'amount' => 1250,
    ]);

    $payload = [
        'status' => 'COMPLETED',
        'invoice_id' => 'INV123',
        'metadata' => [
            'transaction_uuid' => $transaction->uuid,
        ],
    ];

    $response = $this->postJson('/api/payment/webhook', $payload, [
        'RT-UDDOKTAPAY-API-KEY' => 'test-api-key',
    ]);

    $response->assertStatus(200);
    expect($order->refresh()->status)->toBe(OrderStatus::Pending) // Assuming it doesn't change from pending or maybe stays pending? Wait, fulfillment sets failed
        ->and($order->fulfillment_status)->toBe(FulfillmentStatus::Failed)
        ->and($this->customer->refresh()->balance)->toEqual(1250);
});

it('renders filament resource properly', function () {
    $user = User::factory()->create();

    // Assign super_admin role directly to the user (bypass permissions caching issues in tests)
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user->assignRole($role);

    $this->actingAs($user);

    $transaction = PaymentTransaction::factory()->completed()->create();

    $this->get(PaymentTransactionResource::getUrl('index'))
        ->assertSuccessful()
        ->assertSee($transaction->uuid)
        ->assertSee(number_format($transaction->amount, 2));
});
