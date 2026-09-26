<?php

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('super admin can access the admin panel', function () {
    $admin = User::factory()->create([
        'status' => true,
    ]);
    $admin->assignRole('super_admin');

    $panel = Filament::getPanel('admin');

    expect($admin->canAccessPanel($panel))->toBeTrue();
});

test('inactive user cannot access the admin panel', function () {
    $bannedAdmin = User::factory()->create([
        'status' => false,
    ]);
    $bannedAdmin->assignRole('super_admin');

    $panel = Filament::getPanel('admin');

    expect($bannedAdmin->canAccessPanel($panel))->toBeFalse();
});

test('regular user without roles cannot access the admin panel', function () {
    $regularUser = User::factory()->create([
        'status' => true,
    ]);

    $panel = Filament::getPanel('admin');

    expect($regularUser->canAccessPanel($panel))->toBeFalse();
});

test('manager role can access admin panel but has restricted permissions', function () {
    $manager = User::factory()->create([
        'status' => true,
    ]);
    $manager->assignRole('manager');

    $panel = Filament::getPanel('admin');

    expect($manager->canAccessPanel($panel))->toBeTrue()
        ->and($manager->can('ViewAny:User'))->toBeTrue()
        ->and($manager->can('Create:User'))->toBeFalse()
        ->and($manager->can('Delete:User'))->toBeFalse();
});

test('super admin bypasses all authorization gates', function () {
    $superAdmin = User::factory()->create([
        'status' => true,
    ]);
    $superAdmin->assignRole('super_admin');

    expect($superAdmin->can('ViewAny:User'))->toBeTrue()
        ->and($superAdmin->can('Create:User'))->toBeTrue()
        ->and($superAdmin->can('Delete:User'))->toBeTrue()
        ->and($superAdmin->can('Create:Role'))->toBeTrue()
        ->and($superAdmin->can('Delete:Role'))->toBeTrue();
});

test('admin user can be created with hashed password and assigned roles', function () {
    $user = User::create([
        'name' => 'Support Agent',
        'email' => 'agent@example.com',
        'password' => 'secret12345',
        'status' => true,
    ]);

    $user->assignRole('manager');

    expect($user->status)->toBeTrue()
        ->and(Hash::check('secret12345', $user->password))->toBeTrue()
        ->and($user->hasRole('manager'))->toBeTrue()
        ->and($user->hasRole('super_admin'))->toBeFalse();
});

test('super admin can view user management page in admin panel', function () {
    $admin = User::factory()->create([
        'status' => true,
    ]);
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->get('/admin/users')
        ->assertSuccessful();
});

test('super admin can view role management page in admin panel', function () {
    $admin = User::factory()->create([
        'status' => true,
    ]);
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->get('/admin/shield/roles')
        ->assertSuccessful();
});

test('unauthenticated visitor is redirected to login from admin routes', function () {
    $this->get('/admin/users')
        ->assertRedirect('/admin/login');
});

test('super admin can create user via modal on list page', function () {
    $admin = User::factory()->create([
        'status' => true,
    ]);
    $admin->assignRole('super_admin');

    Livewire::actingAs($admin)
        ->test(ListUsers::class)
        ->callAction('create', [
            'name' => 'Modal Created User',
            'email' => 'modal_created@example.com',
            'password' => 'secret123',
            'status' => true,
        ])
        ->assertHasNoActionErrors();

    expect(User::where('email', 'modal_created@example.com')->exists())->toBeTrue();
});

test('super admin can edit user via modal on list page', function () {
    $admin = User::factory()->create([
        'status' => true,
    ]);
    $admin->assignRole('super_admin');

    $targetUser = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
        'status' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(ListUsers::class)
        ->callTableAction('edit', $targetUser, [
            'name' => 'Updated Modal Name',
        ])
        ->assertHasNoTableActionErrors();

    expect($targetUser->fresh()->name)->toBe('Updated Modal Name');
});

test('super admin can view user via modal on list page', function () {
    $admin = User::factory()->create([
        'status' => true,
    ]);
    $admin->assignRole('super_admin');

    $targetUser = User::factory()->create([
        'name' => 'Viewable User',
        'email' => 'viewable@example.com',
        'status' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(ListUsers::class)
        ->assertTableActionVisible('view', $targetUser);
});
