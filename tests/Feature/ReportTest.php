<?php

use App\Filament\Pages\SalesReportPage;
use App\Filament\Widgets\LowStockWidget;
use App\Filament\Widgets\OrderStatusChartWidget;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\TopProductsWidget;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $this->managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
    $this->adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    Permission::firstOrCreate(['name' => 'view_reports', 'guard_name' => 'web']);

    $this->superAdminRole->givePermissionTo('view_reports');
    $this->managerRole->givePermissionTo('view_reports');
    // Admin does NOT get view_reports
});

it('allows super admin and manager to access sales report page', function () {
    $superAdmin = User::factory()->create()->assignRole('super_admin');
    $manager = User::factory()->create()->assignRole('manager');
    $admin = User::factory()->create()->assignRole('admin');

    actingAs($superAdmin)
        ->get(SalesReportPage::getUrl())
        ->assertSuccessful();

    actingAs($manager)
        ->get(SalesReportPage::getUrl())
        ->assertSuccessful();

    actingAs($admin)
        ->get(SalesReportPage::getUrl())
        ->assertForbidden();
});

it('renders dashboard widgets without crashing', function () {
    $superAdmin = User::factory()->create()->assignRole('super_admin');

    Livewire\Livewire::actingAs($superAdmin)
        ->test(StatsOverviewWidget::class)
        ->assertSuccessful()
        ->assertSee('Today');

    Livewire\Livewire::actingAs($superAdmin)
        ->test(RevenueChartWidget::class)
        ->assertSuccessful();

    Livewire\Livewire::actingAs($superAdmin)
        ->test(OrderStatusChartWidget::class)
        ->assertSuccessful();

    Livewire\Livewire::actingAs($superAdmin)
        ->test(TopProductsWidget::class)
        ->assertSuccessful();

    Livewire\Livewire::actingAs($superAdmin)
        ->test(LowStockWidget::class)
        ->assertSuccessful();
});

it('renders sales report table with order and variant records', function () {
    $superAdmin = User::factory()->create()->assignRole('super_admin');
    $customer = Customer::factory()->create(['name' => 'John Doe']);
    $product = Product::factory()->create(['name' => 'Windows 11 Pro']);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'duration_name' => 'Lifetime License',
    ]);

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'total_amount' => 1500,
        'cost_price' => 800,
        'quantity' => 1,
    ]);

    Livewire\Livewire::actingAs($superAdmin)
        ->test(SalesReportPage::class)
        ->assertSuccessful()
        ->assertSee($order->order_number)
        ->assertSee('Windows 11 Pro')
        ->assertSee('Lifetime License')
        ->assertSee('John Doe')
        ->call('setRightTab', 'deposits')
        ->assertSet('activeRightTab', 'deposits')
        ->call('resetFilters')
        ->assertSet('productId', null);
});
