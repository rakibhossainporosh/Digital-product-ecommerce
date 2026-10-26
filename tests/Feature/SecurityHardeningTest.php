<?php

use App\Models\Customer;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(SettingSeeder::class);
    RateLimiter::clear('customer-login|127.0.0.1');
    RateLimiter::clear('customer-register|127.0.0.1');
});

test('http responses include owasp security headers', function (): void {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-XSS-Protection', '1; mode=block');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

test('login is strictly rate limited against brute force attacks', function (): void {
    Customer::factory()->create([
        'email' => 'victim@example.com',
        'password' => Hash::make('CorrectPassword123!'),
    ]);

    // Attempt 5 incorrect passwords
    for ($i = 0; $i < 5; $i++) {
        $response = $this->post('/login', [
            'email' => 'victim@example.com',
            'password' => 'WrongPassword',
        ]);
        $response->assertSessionHasErrors('email');
    }

    // 6th attempt should be blocked with rate limiting error
    $response = $this->post('/login', [
        'email' => 'victim@example.com',
        'password' => 'CorrectPassword123!',
    ]);

    $response->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))->toContain('Too many login attempts');
});

test('registration is rate limited per ip address', function (): void {
    for ($i = 0; $i < 5; $i++) {
        $this->post('/register', [
            'name' => "User {$i}",
            'email' => "user{$i}@example.com",
            'whatsapp_number' => "0170000000{$i}",
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);
        auth('customer')->logout();
    }

    // 6th attempt from same IP should be blocked
    $response = $this->post('/register', [
        'name' => 'Spam Bot',
        'email' => 'spambot@example.com',
        'whatsapp_number' => '01799999999',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))->toContain('Too many registration requests');
});

test('payment webhook rejects invalid api key using constant time check', function (): void {
    $response = $this->postJson('/api/payment/webhook', [
        'status' => 'COMPLETED',
        'metadata' => ['transaction_uuid' => 'TRX-123456'],
    ], [
        'RT-UDDOKTAPAY-API-KEY' => 'invalid-fake-key-999',
    ]);

    $response->assertStatus(401);
});
