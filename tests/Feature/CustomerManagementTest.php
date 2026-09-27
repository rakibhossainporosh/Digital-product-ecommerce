<?php

use App\Enums\TransactionDirection;
use App\Enums\WalletTransactionType;
use App\Exceptions\CustomerAccountLockedException;
use App\Exceptions\InsufficientWalletBalanceException;
use App\Models\Customer;
use App\Models\ProductVariant;
use App\Models\ResellerPrice;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('super admin can view customers page in admin panel with 4 KPI cards', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    Customer::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get('/admin/customers')
        ->assertSuccessful()
        ->assertSee('Customers')
        ->assertSee('Total Customers')
        ->assertSee('Wallet Float / Liability')
        ->assertSee('Active Resellers')
        ->assertSee('Banned / Suspended');
});

test('customer model auto generates unique referral code on creation', function () {
    $customer1 = Customer::factory()->create(['referral_code' => null]);
    $customer2 = Customer::factory()->create(['referral_code' => null]);

    expect($customer1->referral_code)->not->toBeNull()
        ->and($customer1->referral_code)->toStartWith('REF')
        ->and($customer2->referral_code)->not->toBe($customer1->referral_code);
});

test('wallet balance is guarded against mass assignment', function () {
    $customer = Customer::create([
        'name' => 'Hacker Test',
        'email' => 'hacker@example.com',
        'balance' => 999999.00, // Should be ignored because balance is guarded!
    ]);

    expect((float) $customer->fresh()->balance)->toBe(0.00);
});

test('wallet service atomically credits customer balance and creates ledger entry', function () {
    $service = app(WalletService::class);
    $customer = Customer::factory()->create(['balance' => 200.00]);

    $transaction = $service->credit(
        customer: $customer,
        amount: 500.00,
        type: WalletTransactionType::Deposit,
        description: 'Test bKash Deposit',
        referenceId: 'DEP-12345'
    );

    expect((float) $customer->fresh()->balance)->toBe(700.00)
        ->and($transaction->direction)->toBe(TransactionDirection::Credit)
        ->and((float) $transaction->amount)->toBe(500.00)
        ->and((float) $transaction->opening_balance)->toBe(200.00)
        ->and((float) $transaction->closing_balance)->toBe(700.00)
        ->and($transaction->reference_id)->toBe('DEP-12345');
});

test('wallet service atomically debits customer balance and creates ledger entry', function () {
    $service = app(WalletService::class);
    $customer = Customer::factory()->create(['balance' => 1000.00]);

    $transaction = $service->debit(
        customer: $customer,
        amount: 350.00,
        type: WalletTransactionType::Purchase,
        description: 'License Key Purchase',
        referenceId: 'ORD-99887'
    );

    expect((float) $customer->fresh()->balance)->toBe(650.00)
        ->and($transaction->direction)->toBe(TransactionDirection::Debit)
        ->and((float) $transaction->amount)->toBe(350.00)
        ->and((float) $transaction->opening_balance)->toBe(1000.00)
        ->and((float) $transaction->closing_balance)->toBe(650.00)
        ->and($transaction->reference_id)->toBe('ORD-99887');
});

test('wallet service prevents debit when balance is insufficient and throws exception', function () {
    $service = app(WalletService::class);
    $customer = Customer::factory()->create(['balance' => 100.00]);

    expect(fn () => $service->debit(
        customer: $customer,
        amount: 250.00,
        type: WalletTransactionType::Purchase,
        description: 'Overdrawn purchase'
    ))->toThrow(InsufficientWalletBalanceException::class);

    // Balance remains unmodified
    expect((float) $customer->fresh()->balance)->toBe(100.00)
        ->and(WalletTransaction::where('customer_id', $customer->id)->count())->toBe(0);
});

test('wallet service admin adjust records admin id and reason for audit', function () {
    $service = app(WalletService::class);
    $admin = User::factory()->create();
    $customer = Customer::factory()->create(['balance' => 50.00]);

    $transaction = $service->adminAdjust(
        customer: $customer,
        amount: 150.00,
        direction: TransactionDirection::Credit,
        reason: 'Manual compensation for downtime',
        admin: $admin
    );

    expect((float) $customer->fresh()->balance)->toBe(200.00)
        ->and($transaction->type)->toBe(WalletTransactionType::AdminAdjust)
        ->and($transaction->admin_id)->toBe($admin->id)
        ->and($transaction->description)->toContain('Manual compensation for downtime')
        ->and($transaction->metadata['reason'])->toBe('Manual compensation for downtime');
});

test('wallet service prevents transactions on banned customer account', function () {
    $service = app(WalletService::class);
    $bannedCustomer = Customer::factory()->banned()->create(['balance' => 500.00]);

    expect(fn () => $service->credit(
        customer: $bannedCustomer,
        amount: 100.00,
        type: WalletTransactionType::Deposit,
        description: 'Blocked deposit'
    ))->toThrow(CustomerAccountLockedException::class);

    expect(fn () => $service->debit(
        customer: $bannedCustomer,
        amount: 100.00,
        type: WalletTransactionType::Purchase,
        description: 'Blocked purchase'
    ))->toThrow(CustomerAccountLockedException::class);
});

test('wallet service verifies ledger integrity against sum of transactions', function () {
    $service = app(WalletService::class);
    $customer = Customer::factory()->create(['balance' => 0.00]);

    $service->credit($customer, 1000.00, WalletTransactionType::Deposit, 'Deposit 1');
    $service->debit($customer, 300.00, WalletTransactionType::Purchase, 'Purchase 1');
    $service->credit($customer, 50.00, WalletTransactionType::Refund, 'Refund 1');

    $integrity = $service->verifyLedgerIntegrity($customer);

    expect($integrity['is_valid'])->toBeTrue()
        ->and($integrity['current_balance'])->toBe(750.00)
        ->and($integrity['calculated_balance'])->toBe(750.00)
        ->and($integrity['difference'])->toBe(0.00);
});

test('reseller customer can have custom variant pricing rules', function () {
    $variant = ProductVariant::factory()->create([
        'regular_price' => 1000.00,
        'offer_price' => 900.00,
    ]);

    $reseller = Customer::factory()->reseller(15.00)->create();

    $customPrice = ResellerPrice::create([
        'customer_id' => $reseller->id,
        'product_variant_id' => $variant->id,
        'discount_percentage' => 20.00,
        'custom_price' => 720.00,
    ]);

    expect($reseller->isReseller())->toBeTrue()
        ->and((float) $reseller->reseller_discount)->toBe(15.00)
        ->and($reseller->resellerPrices)->toHaveCount(1)
        ->and((float) $reseller->resellerPrices->first()->custom_price)->toBe(720.00);
});
