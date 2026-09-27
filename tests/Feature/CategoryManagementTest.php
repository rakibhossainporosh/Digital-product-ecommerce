<?php

use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('super admin can view category list page in admin panel', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->get('/admin/categories')
        ->assertSuccessful();
});

test('unauthenticated visitor is redirected to login from categories page', function () {
    $this->get('/admin/categories')
        ->assertRedirect('/admin/login');
});

test('super admin can create a root category via modal on list page', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    Livewire::actingAs($admin)
        ->test(ListCategories::class)
        ->callAction('create', [
            'name' => 'Antivirus Software',
            'slug' => 'antivirus-software',
            'description' => 'Security software and activation keys',
            'sort_order' => 10,
            'status' => true,
        ])
        ->assertHasNoActionErrors();

    $category = Category::where('slug', 'antivirus-software')->first();

    expect($category)->not->toBeNull()
        ->and($category->name)->toBe('Antivirus Software')
        ->and($category->isRoot())->toBeTrue()
        ->and($category->status)->toBeTrue();
});

test('super admin can create a subcategory with parent category', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    $parent = Category::factory()->create([
        'name' => 'Gaming Tools',
        'slug' => 'gaming-tools',
    ]);

    Livewire::actingAs($admin)
        ->test(ListCategories::class)
        ->callAction('create', [
            'name' => 'Free Fire VIP',
            'slug' => 'free-fire-vip',
            'parent_id' => $parent->id,
            'status' => true,
        ])
        ->assertHasNoActionErrors();

    $child = Category::where('slug', 'free-fire-vip')->first();

    expect($child)->not->toBeNull()
        ->and($child->parent_id)->toBe($parent->id)
        ->and($child->isChild())->toBeTrue()
        ->and($parent->children()->count())->toBe(1);
});

test('super admin can edit category via modal on list page', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    $category = Category::factory()->create([
        'name' => 'Old Category Name',
        'slug' => 'old-category-name',
        'status' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(ListCategories::class)
        ->callTableAction('edit', $category, [
            'name' => 'Updated Category Name',
            'slug' => 'updated-category-name',
        ])
        ->assertHasNoTableActionErrors();

    expect($category->fresh()->name)->toBe('Updated Category Name')
        ->and($category->fresh()->slug)->toBe('updated-category-name');
});

test('super admin can soft delete and restore a category', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    $category = Category::factory()->create([
        'name' => 'Deletable Category',
        'slug' => 'deletable-category',
    ]);

    Livewire::actingAs($admin)
        ->test(ListCategories::class)
        ->callTableAction('delete', $category)
        ->assertHasNoTableActionErrors();

    expect($category->fresh()->trashed())->toBeTrue();

    Livewire::actingAs($admin)
        ->test(ListCategories::class)
        ->callTableAction('restore', $category)
        ->assertHasNoTableActionErrors();

    expect($category->fresh()->trashed())->toBeFalse();
});

test('active scope only returns active categories', function () {
    Category::factory()->create(['status' => true, 'slug' => 'active-cat']);
    Category::factory()->inactive()->create(['slug' => 'inactive-cat']);

    $activeCategories = Category::active()->get();

    expect($activeCategories->pluck('slug')->all())->toContain('active-cat')
        ->and($activeCategories->pluck('slug')->all())->not->toContain('inactive-cat');
});

test('view action is visible in categories table', function () {
    $admin = User::factory()->create(['status' => true]);
    $admin->assignRole('super_admin');

    $category = Category::factory()->create(['name' => 'Viewable Category']);

    Livewire::actingAs($admin)
        ->test(ListCategories::class)
        ->assertTableActionVisible('view', $category);
});
