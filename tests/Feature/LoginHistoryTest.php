<?php

use App\Models\LoginHistory;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('successful login event automatically creates success login history record', function () {
    $user = User::factory()->create([
        'email' => 'admin.test@example.com',
        'status' => true,
    ]);

    event(new Login('web', $user, false));

    $log = LoginHistory::where('email', 'admin.test@example.com')->first();

    expect($log)->not->toBeNull()
        ->and($log->status)->toBe('success')
        ->and($log->user_id)->toBe($user->id)
        ->and($log->failure_reason)->toBeNull()
        ->and($log->ip_address)->not->toBeEmpty();
});

test('failed login event automatically creates failed login history record', function () {
    event(new Failed('web', null, ['email' => 'unauthorized@example.com', 'password' => 'wrong']));

    $log = LoginHistory::where('email', 'unauthorized@example.com')->first();

    expect($log)->not->toBeNull()
        ->and($log->status)->toBe('failed')
        ->and($log->user_id)->toBeNull()
        ->and($log->failure_reason)->toBe('Invalid credentials')
        ->and($log->ip_address)->not->toBeEmpty();
});

test('super admin can access audit logs page in filament', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    LoginHistory::factory()->count(3)->create();
    LoginHistory::factory()->failed()->count(2)->create();

    $this->actingAs($admin)
        ->get('/admin/login-histories')
        ->assertSuccessful()
        ->assertSee('Audit Logs')
        ->assertSee('Total Audit Records');
});

test('super admin can view single audit log record detail page', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    $log = LoginHistory::factory()->create([
        'email' => 'audited.user@example.com',
        'status' => 'success',
    ]);

    $this->actingAs($admin)
        ->get("/admin/login-histories/{$log->id}")
        ->assertSuccessful()
        ->assertSee('audited.user@example.com')
        ->assertSee('Authentication Event Details');
});
