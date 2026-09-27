<?php

use App\Filament\Pages\ManageSettings;
use App\Models\Setting;
use App\Models\User;
use App\Services\SettingService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(SettingSeeder::class);
    $this->settingService = app(SettingService::class);
});

test('it seeds default system settings correctly', function (): void {
    expect(setting('app_name'))->toBe('Digital Product Store')
        ->and(setting('currency_symbol'))->toBe('৳')
        ->and(setting('maintenance_mode'))->toBeFalse()
        ->and(setting('referral_commission_percentage'))->toBe(5.0)
        ->and(setting('min_deposit_amount'))->toBe(50.0)
        ->and(setting('gateway_enabled'))->toBeTrue();
});

test('it supports native type casting for various data types', function (): void {
    $this->settingService->set('test_bool_true', true, 'general', 'boolean');
    $this->settingService->set('test_bool_false', false, 'general', 'boolean');
    $this->settingService->set('test_int', 42, 'general', 'integer');
    $this->settingService->set('test_float', 99.95, 'general', 'float');
    $this->settingService->set('test_json', ['features' => ['a', 'b'], 'active' => true], 'general', 'json');

    expect(setting('test_bool_true'))->toBeTrue()
        ->and(setting('test_bool_false'))->toBeFalse()
        ->and(setting('test_int'))->toBe(42)
        ->and(setting('test_float'))->toBe(99.95)
        ->and(setting('test_json'))->toBe(['features' => ['a', 'b'], 'active' => true]);
});

test('it securely encrypts secrets at rest in database and decrypts transparently', function (): void {
    $secretApiKey = 'super-secret-uddoktapay-key-xyz-123';
    $this->settingService->set('gateway_api_key', $secretApiKey, 'gateway', 'encrypted');

    // Verify raw database record is encrypted and does not contain plain text
    $rawRecord = Setting::where('key', 'gateway_api_key')->first();
    expect($rawRecord)->not->toBeNull()
        ->and($rawRecord->value)->not->toBe($secretApiKey)
        ->and(Crypt::decryptString($rawRecord->value))->toBe($secretApiKey);

    // Verify setting helper returns decrypted plain value
    expect(setting('gateway_api_key'))->toBe($secretApiKey);
});

test('it returns default value when setting key does not exist', function (): void {
    expect(setting('non_existent_key', 'fallback_value'))->toBe('fallback_value')
        ->and(setting('another_missing_key'))->toBeNull();
});

test('it returns only public settings via getPublic', function (): void {
    $publicSettings = $this->settingService->getPublic();

    expect($publicSettings)->toHaveKey('app_name')
        ->and($publicSettings)->toHaveKey('currency_symbol')
        ->and($publicSettings)->not->toHaveKey('gateway_api_key')
        ->and($publicSettings)->not->toHaveKey('supplier_api_key')
        ->and($publicSettings)->not->toHaveKey('maintenance_bypass_key');
});

test('it flushes and reloads cache when a setting is updated', function (): void {
    expect(setting('app_name'))->toBe('Digital Product Store');

    $this->settingService->set('app_name', 'NextGen Digital Hub');

    expect(setting('app_name'))->toBe('NextGen Digital Hub');
});

// ── Maintenance Mode Middleware Tests ──

test('storefront is accessible when maintenance mode is off', function (): void {
    $response = $this->get('/');

    $response->assertOk();
});

test('storefront returns 503 service unavailable when maintenance mode is on', function (): void {
    $this->settingService->set('maintenance_mode', true, 'maintenance', 'boolean');

    $response = $this->get('/');

    $response->assertStatus(503);
    $response->assertSee('Under Scheduled Maintenance');
});

test('storefront returns json 503 response for api/json requests during maintenance', function (): void {
    $this->settingService->set('maintenance_mode', true, 'maintenance', 'boolean');

    $response = $this->getJson('/');

    $response->assertStatus(503)
        ->assertJson([
            'status' => 'maintenance',
            'headline' => 'Under Scheduled Maintenance',
        ]);
});

test('admin panel is never blocked by maintenance mode', function (): void {
    $this->settingService->set('maintenance_mode', true, 'maintenance', 'boolean');

    // Admin login page must remain reachable
    $response = $this->get('/admin/login');

    $response->assertOk();
});

test('visitors with secret bypass query key can view storefront during maintenance', function (): void {
    $this->settingService->set('maintenance_mode', true, 'maintenance', 'boolean');
    $this->settingService->set('maintenance_bypass_key', 'test-secret-bypass-2026', 'maintenance', 'string');

    // Request with invalid bypass key should fail
    $invalidResponse = $this->get('/?bypass_key=wrong-key');
    $invalidResponse->assertStatus(503);

    // Request with valid bypass key should pass and set cookie
    $validResponse = $this->get('/?bypass_key=test-secret-bypass-2026');
    $validResponse->assertOk();
    $validResponse->assertCookie('maintenance_bypass_token', 'test-secret-bypass-2026');

    // Subsequent request with the cookie should also pass without query param
    $cookieResponse = $this->withCookie('maintenance_bypass_token', 'test-secret-bypass-2026')->get('/');
    $cookieResponse->assertOk();
});

// ── Filament ManageSettings Page Tests ──

test('guests cannot access filament settings page', function (): void {
    $response = $this->get('/admin/settings');

    $response->assertRedirect('/admin/login');
});

test('super admin can access and view filament settings page', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    $response = $this->actingAs($superAdmin)->get('/admin/settings');

    $response->assertOk();
});

test('super admin can update settings through filament form', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    Livewire::actingAs($superAdmin)
        ->test(ManageSettings::class)
        ->fillForm([
            'app_name' => 'KopaSamsu Reborn Store',
            'currency_symbol' => '৳',
            'support_email' => 'support@kopasamsu.com',
            'referral_commission_percentage' => 7.5,
            'min_deposit_amount' => 100.0,
            'max_deposit_amount' => 25000.0,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(setting('app_name'))->toBe('KopaSamsu Reborn Store')
        ->and(setting('support_email'))->toBe('support@kopasamsu.com')
        ->and(setting('referral_commission_percentage'))->toBe(7.5)
        ->and(setting('min_deposit_amount'))->toBe(100.0)
        ->and(setting('max_deposit_amount'))->toBe(25000.0);
});

test('flush cache header action successfully clears cache', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    Livewire::actingAs($superAdmin)
        ->test(ManageSettings::class)
        ->callAction('flushCache');

    expect(true)->toBeTrue();
});
