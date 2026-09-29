<?php

namespace App\Filament\Pages;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\WalletTransactionType;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class SalesReportPage extends Page
{
    use WithPagination;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance & Wallet';

    protected static ?string $navigationLabel = 'Sell Report';

    protected static ?string $title = 'Sell Report';

    protected string $view = 'filament.pages.sales-report-page';

    protected static ?int $sort = 5;

    public ?string $startDate = null;

    public ?string $endDate = null;

    public ?int $productId = null;

    public ?int $variantId = null;

    public string $activeTab = 'direct_sales'; // 'direct_sales', 'wallet_sales', or 'wallet_deposits'

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_reports') ?? false;
    }

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function updatedProductId(): void
    {
        $this->variantId = null;
        $this->resetPage('direct_page');
        $this->resetPage('wallet_page');
        $this->resetPage('deposits_page');
    }

    public function updatedVariantId(): void
    {
        if ($this->variantId) {
            $variant = ProductVariant::find($this->variantId);
            if ($variant) {
                $this->productId = $variant->product_id;
            }
        }
        $this->resetPage('direct_page');
        $this->resetPage('wallet_page');
        $this->resetPage('deposits_page');
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['direct_sales', 'wallet_sales', 'wallet_deposits'], true) ? $tab : 'direct_sales';
    }

    public function resetFilters(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
        $this->productId = null;
        $this->variantId = null;
        $this->activeTab = 'direct_sales';

        $this->resetPage('direct_page');
        $this->resetPage('wallet_page');
        $this->resetPage('deposits_page');
    }

    public function applyFilters(): void
    {
        $this->resetPage('direct_page');
        $this->resetPage('wallet_page');
        $this->resetPage('deposits_page');
    }

    public function getSelectedProductProperty(): ?Product
    {
        return $this->productId ? Product::find($this->productId) : null;
    }

    public function getSelectedVariantProperty(): ?ProductVariant
    {
        return $this->variantId ? ProductVariant::find($this->variantId) : null;
    }

    public function getAvailableVariantsProperty(): Collection
    {
        if ($this->productId) {
            return ProductVariant::where('product_id', $this->productId)
                ->orderBy('duration_days')
                ->get();
        }

        return ProductVariant::with('product')
            ->orderBy('duration_days')
            ->get();
    }

    protected function getParsedDates(): array
    {
        $start = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : now()->startOfMonth()->startOfDay();
        $end = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : now()->endOfDay();

        return [$start, $end];
    }

    protected function getDirectOrdersQuery(): Builder
    {
        [$start, $end] = $this->getParsedDates();

        $query = Order::query()
            ->with(['customer', 'product', 'productVariant'])
            ->where('status', OrderStatus::Completed)
            ->where(function (Builder $q): void {
                $q->where('gateway_amount_paid', '>', 0)
                    ->orWhere(function (Builder $sub): void {
                        $sub->where('payment_method', '!=', 'wallet')
                            ->where('payment_method', '!=', PaymentMethod::Wallet->value);
                    });
            })
            ->whereBetween('created_at', [$start, $end]);

        if ($this->variantId) {
            $query->where('product_variant_id', $this->variantId);
        } elseif ($this->productId) {
            $query->where('product_id', $this->productId);
        }

        return $query;
    }

    protected function getWalletOrdersQuery(): Builder
    {
        [$start, $end] = $this->getParsedDates();

        $query = Order::query()
            ->with(['customer', 'product', 'productVariant'])
            ->where('status', OrderStatus::Completed)
            ->where(function (Builder $q): void {
                $q->where('wallet_amount_paid', '>', 0)
                    ->orWhere('payment_method', 'wallet')
                    ->orWhere('payment_method', PaymentMethod::Wallet->value);
            })
            ->whereBetween('created_at', [$start, $end]);

        if ($this->variantId) {
            $query->where('product_variant_id', $this->variantId);
        } elseif ($this->productId) {
            $query->where('product_id', $this->productId);
        }

        return $query;
    }

    protected function getWalletDepositsQuery(): ?Builder
    {
        if ($this->productId || $this->variantId) {
            return null;
        }

        [$start, $end] = $this->getParsedDates();

        return WalletTransaction::query()
            ->with('customer')
            ->whereBetween('created_at', [$start, $end])
            ->where(function (Builder $query): void {
                $query->where(function (Builder $q): void {
                    $q->where('type', WalletTransactionType::Deposit->value)
                        ->where(function (Builder $sq): void {
                            $sq->whereNull('description')
                                ->orWhere('description', 'not like', 'Referral Bonus%');
                        });
                })->orWhere('type', WalletTransactionType::AdminAdjust->value);
            });
    }

    public function getViewData(): array
    {
        $directQuery = $this->getDirectOrdersQuery();
        $walletQuery = $this->getWalletOrdersQuery();
        $depositsQuery = $this->getWalletDepositsQuery();

        $totalDirectSales = (float) (clone $directQuery)->sum(
            DB::raw('CASE WHEN gateway_amount_paid > 0 THEN gateway_amount_paid WHEN payment_method != "wallet" THEN total_amount ELSE 0 END')
        );
        $directOrdersCount = (clone $directQuery)->count();
        $directOrders = (clone $directQuery)->orderByDesc('created_at')->paginate(10, ['*'], 'direct_page');

        $totalWalletSales = (float) (clone $walletQuery)->sum(
            DB::raw('CASE WHEN wallet_amount_paid > 0 THEN wallet_amount_paid WHEN payment_method = "wallet" THEN total_amount ELSE 0 END')
        );
        $walletOrdersCount = (clone $walletQuery)->count();
        $walletOrders = (clone $walletQuery)->orderByDesc('created_at')->paginate(10, ['*'], 'wallet_page');

        if ($depositsQuery) {
            $totalWalletDeposits = (float) (clone $depositsQuery)->sum('amount');
            $walletDepositsCount = (clone $depositsQuery)->count();
            $walletTransactions = (clone $depositsQuery)->orderByDesc('created_at')->paginate(10, ['*'], 'deposits_page');
        } else {
            $totalWalletDeposits = 0;
            $walletDepositsCount = 0;
            $walletTransactions = null;
        }

        $totalProductSales = $totalDirectSales + $totalWalletSales;
        $totalOrdersCount = $directOrdersCount + $walletOrdersCount;
        $totalRevenue = $totalDirectSales + $totalWalletDeposits;

        $products = Product::query()->orderBy('name')->get();

        return [
            'totalDirectSales' => $totalDirectSales,
            'directOrdersCount' => $directOrdersCount,
            'directOrders' => $directOrders,
            'totalWalletSales' => $totalWalletSales,
            'walletOrdersCount' => $walletOrdersCount,
            'walletOrders' => $walletOrders,
            'totalWalletDeposits' => $totalWalletDeposits,
            'walletDepositsCount' => $walletDepositsCount,
            'walletTransactions' => $walletTransactions,
            'totalProductSales' => $totalProductSales,
            'totalOrdersCount' => $totalOrdersCount,
            'totalRevenue' => $totalRevenue,
            'products' => $products,
            'selectedProduct' => $this->selectedProduct,
            'selectedVariant' => $this->selectedVariant,
        ];
    }
}
