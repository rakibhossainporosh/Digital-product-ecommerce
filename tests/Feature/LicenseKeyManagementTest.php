<?php

use App\Enums\LicenseKeyStatus;
use App\Models\LicenseKey;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\LicenseKeyImportService;
use App\Services\LicenseKeyStockService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('super admin can view license keys page in admin panel and see key metrics cards', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    $variant = ProductVariant::factory()->create();
    LicenseKey::factory()->count(3)->create([
        'product_variant_id' => $variant->id,
        'status' => LicenseKeyStatus::Available,
    ]);

    $this->actingAs($admin)
        ->get('/admin/license-keys')
        ->assertSuccessful()
        ->assertSee('License Keys')
        ->assertSee('Total Keys')
        ->assertSee('Available Stock')
        ->assertSee('Total Sold')
        ->assertSee('Low Stock Variants');
});

test('license key raw string is encrypted at rest in database and masked on model accessor', function () {
    $variant = ProductVariant::factory()->create();
    $rawKey = 'VIP-TOPSECRET-9988-7766';

    $key = LicenseKey::create([
        'product_variant_id' => $variant->id,
        'key' => $rawKey,
        'status' => LicenseKeyStatus::Available,
    ]);

    // Model decrypts transparently
    expect($key->key)->toBe($rawKey)
        ->and($key->masked_key)->toBe('VIP--••••••••-7766');

    // Database raw column is encrypted ciphertext (AES-256 payload)
    $rawDbValue = (string) DB::table('license_keys')->where('id', $key->id)->value('key');
    expect($rawDbValue)->not->toBe($rawKey)
        ->and(base64_decode($rawDbValue))->toContain('"iv":')
        ->and(base64_decode($rawDbValue))->toContain('"value":');
});

test('bulk import service sanitizes supplier bracket notes, dates, and comments', function () {
    $variant = ProductVariant::factory()->create();
    $service = new LicenseKeyImportService;

    $rawInput = '
        VIP-KEY-AAAA-1111 [Expires: 2026-12-31]
        VIP-KEY-BBBB-2222 (Supplier: Premium Shop)
        VIP-KEY-CCCC-3333 // Some comment
        VIP-KEY-DDDD-4444 | 30 Days Access
    ';

    $result = $service->import($variant->id, $rawInput);

    expect($result['imported'])->toBe(4)
        ->and($result['valid_keys'])->toBe(4);

    $keys = LicenseKey::where('product_variant_id', $variant->id)->pluck('key')->all();

    expect($keys)->toContain('VIP-KEY-AAAA-1111')
        ->and($keys)->toContain('VIP-KEY-BBBB-2222')
        ->and($keys)->toContain('VIP-KEY-CCCC-3333')
        ->and($keys)->toContain('VIP-KEY-DDDD-4444');
});

test('bulk import service deduplicates within the batch and skips existing DB keys', function () {
    $variant = ProductVariant::factory()->create();
    $service = new LicenseKeyImportService;

    // Seed one existing key in DB
    LicenseKey::create([
        'product_variant_id' => $variant->id,
        'key' => 'VIP-EXISTING-KEY',
        'status' => LicenseKeyStatus::Available,
    ]);

    $rawInput = '
        VIP-EXISTING-KEY
        VIP-NEW-KEY-1
        VIP-NEW-KEY-1
        VIP-NEW-KEY-2
    ';

    $result = $service->import($variant->id, $rawInput);

    expect($result['total_lines'])->toBe(6)
        ->and($result['valid_keys'])->toBe(3)
        ->and($result['duplicates_in_batch'])->toBe(1)
        ->and($result['duplicates_in_db'])->toBe(1)
        ->and($result['imported'])->toBe(2);

    expect(LicenseKey::where('product_variant_id', $variant->id)->count())->toBe(3);
});

test('stock service atomically allocates keys and marks them as sold with order ID', function () {
    $variant = ProductVariant::factory()->create();
    $key1 = LicenseKey::factory()->create([
        'product_variant_id' => $variant->id,
        'key' => 'VIP-STOCK-KEY-001',
        'status' => LicenseKeyStatus::Available,
    ]);

    $service = new LicenseKeyStockService;
    $allocated = $service->allocateKey($variant->id, 5555, 1);

    expect($allocated->id)->toBe($key1->id)
        ->and($allocated->status)->toBe(LicenseKeyStatus::Sold)
        ->and($allocated->order_id)->toBe(5555)
        ->and($allocated->sold_at)->not->toBeNull();

    // Available count should now be 0
    expect($variant->fresh()->available_keys_count)->toBe(0);
});

test('stock service throws exception when variant is out of stock', function () {
    $variant = ProductVariant::factory()->create();
    $service = new LicenseKeyStockService;

    expect(fn () => $service->allocateKey($variant->id, 101, 1))
        ->toThrow(RuntimeException::class, "No available license keys found for variant #{$variant->id}");
});

test('stock service can reserve and release keys', function () {
    $variant = ProductVariant::factory()->create();
    $key = LicenseKey::factory()->create([
        'product_variant_id' => $variant->id,
        'status' => LicenseKeyStatus::Available,
    ]);

    $service = new LicenseKeyStockService;
    $reserved = $service->reserveKey($variant->id);

    expect($reserved->id)->toBe($key->id)
        ->and($reserved->status)->toBe(LicenseKeyStatus::Reserved);

    // Release back to available
    $released = $service->releaseKey($key->id);
    expect($released)->toBeTrue()
        ->and($key->fresh()->status)->toBe(LicenseKeyStatus::Available);
});

test('products table accurately shows live stock count badge across variants', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    $product = Product::factory()->create(['name' => 'Apex Pro Tool']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    LicenseKey::factory()->count(15)->create([
        'product_variant_id' => $variant->id,
        'status' => LicenseKeyStatus::Available,
    ]);

    $this->actingAs($admin)
        ->get('/admin/products')
        ->assertSuccessful()
        ->assertSee('15 in stock');
});

test('license keys page can filter by available, sold, and low stock', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    $variant = ProductVariant::factory()->create();

    $availableKey = LicenseKey::factory()->create([
        'product_variant_id' => $variant->id,
        'key' => 'VIP-FILTER-AVAILABLE-KEY',
        'status' => LicenseKeyStatus::Available,
    ]);

    $soldKey = LicenseKey::factory()->create([
        'product_variant_id' => $variant->id,
        'key' => 'VIP-FILTER-SOLD-KEY',
        'status' => LicenseKeyStatus::Sold,
    ]);

    $this->actingAs($admin)
        ->get('/admin/license-keys?filter=available')
        ->assertSuccessful()
        ->assertSee($availableKey->masked_key);

    $this->actingAs($admin)
        ->get('/admin/license-keys?filter=sold')
        ->assertSuccessful()
        ->assertSee($soldKey->masked_key);
});

test('user without license key permission cannot access license keys resource', function () {
    $user = User::factory()->create(['status' => true]);
    // Regular user without permissions

    $this->actingAs($user)
        ->get('/admin/license-keys')
        ->assertForbidden();
});
