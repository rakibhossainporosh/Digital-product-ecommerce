<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\UserAgentParser;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── Main Column (2/3 width) ──
                Group::make([
                    // Callout: Awaiting Key Fulfillment
                    Callout::make('Awaiting License Key Allocation')
                        ->description('This digital order has been paid, but license keys are waiting to be allocated. Click "Retry Fulfillment" above to assign keys from available inventory.')
                        ->color('warning')
                        ->icon('heroicon-m-exclamation-triangle')
                        ->visible(fn (Order $record): bool => $record->canBeFulfilled() && ! $record->isService()),

                    // Callout: Manual Service Fulfillment Needed
                    Callout::make('Manual Service Fulfillment Required')
                        ->description('This is a manual service order (e.g. Game Top-up, UID Injection, or Rooting). Please review the customer submitted data below and click "Complete Service" once finished.')
                        ->color('info')
                        ->icon('heroicon-m-information-circle')
                        ->visible(fn (Order $record): bool => $record->canBeFulfilled() && $record->isService()),

                    // Callout: Order Refunded
                    Callout::make('Order Refunded to Customer Wallet')
                        ->description(fn (Order $record): string => 'This order has been cancelled and refunded to customer wallet. Reason: '.($record->admin_notes ?: 'Manual refund or auto stock exhaustion.'))
                        ->color('danger')
                        ->icon('heroicon-m-arrow-uturn-left')
                        ->visible(fn (Order $record): bool => $record->status === OrderStatus::Refunded),

                    // Section 1: Purchased Items & Line Details
                    Section::make('Purchased Product & Fulfillment Details')
                        ->icon('heroicon-m-shopping-bag')
                        ->description('Item purchased, selected variant tier, and current delivery state')
                        ->schema([
                            Grid::make(['default' => 1, 'sm' => 12])
                                ->schema([
                                    ImageEntry::make('product.image')
                                        ->label('Product Cover')
                                        ->disk('public')
                                        ->square()
                                        ->size(76)
                                        ->defaultImageUrl(fn (Order $record): string => 'https://ui-avatars.com/api/?name='.urlencode($record->product?->name ?? 'Product').'&background=0f172a&color=f82803&bold=true&size=128')
                                        ->extraImgAttributes([
                                            'class' => 'rounded-xl object-cover shadow-sm border border-gray-200 dark:border-white/10 shrink-0',
                                        ])
                                        ->columnSpan(['default' => 12, 'sm' => 2]),

                                    Group::make([
                                        TextEntry::make('product.name')
                                            ->label('Product Title')
                                            ->size(TextSize::Large)
                                            ->weight(FontWeight::Bold)
                                            ->icon('heroicon-m-cube')
                                            ->url(fn (Order $record): ?string => $record->product_id ? url('/admin/products/'.$record->product_id.'/edit') : null)
                                            ->openUrlInNewTab(),

                                        Grid::make(3)->schema([
                                            TextEntry::make('product.category.name')
                                                ->label('Category')
                                                ->badge()
                                                ->color('gray')
                                                ->icon('heroicon-m-folder')
                                                ->placeholder('Uncategorized'),

                                            TextEntry::make('product_type')
                                                ->label('Fulfillment Type')
                                                ->badge()
                                                ->state(fn (Order $record): string => $record->isService() ? 'Manual Service (Top-up)' : 'Digital License Key')
                                                ->color(fn (Order $record): string => $record->isService() ? 'warning' : 'info')
                                                ->icon(fn (Order $record): string => $record->isService() ? 'heroicon-m-wrench' : 'heroicon-m-key'),

                                            TextEntry::make('productVariant.duration_name')
                                                ->label('Selected Variant Plan')
                                                ->badge()
                                                ->color('primary')
                                                ->icon('heroicon-m-sparkles'),
                                        ]),
                                    ])->columnSpan(['default' => 12, 'sm' => 10]),
                                ]),

                            Grid::make(4)
                                ->schema([
                                    TextEntry::make('unit_price')
                                        ->label('Base Unit Price')
                                        ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2))
                                        ->icon('heroicon-m-currency-bangladeshi'),

                                    TextEntry::make('quantity')
                                        ->label('Quantity Purchased')
                                        ->badge()
                                        ->color('gray')
                                        ->formatStateUsing(fn ($state): string => "{$state} units"),

                                    TextEntry::make('subtotal')
                                        ->label('Line Subtotal')
                                        ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2))
                                        ->weight(FontWeight::SemiBold),

                                    TextEntry::make('fulfillment_summary')
                                        ->label('Delivery Progress')
                                        ->badge()
                                        ->state(function (Order $record): string {
                                            if ($record->isService()) {
                                                return $record->isFulfilled() ? 'Service Delivered' : 'Pending Fulfillment';
                                            }
                                            $deliveredKeys = $record->licenseKeys()->count();

                                            return $record->isFulfilled()
                                                ? "Fulfilled ({$deliveredKeys}/{$record->quantity} Keys)"
                                                : "Unfulfilled ({$deliveredKeys}/{$record->quantity} Keys)";
                                        })
                                        ->color(fn (Order $record): string => $record->isFulfilled() ? 'success' : ($record->isPaid() ? 'warning' : 'gray'))
                                        ->icon(fn (Order $record): string => $record->isFulfilled() ? 'heroicon-m-check-circle' : 'heroicon-m-clock'),
                                ]),
                        ]),

                    // Section 2: Financial Calculation & Billing Audit
                    Section::make('Payment & Financial Reconciliation')
                        ->icon('heroicon-m-banknotes')
                        ->description('Itemized financial breakdown and gateway transaction details')
                        ->schema([
                            Grid::make(4)
                                ->schema([
                                    TextEntry::make('subtotal')
                                        ->label('Items Subtotal')
                                        ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2)),

                                    TextEntry::make('discount_amount')
                                        ->label('Discount Applied')
                                        ->formatStateUsing(function ($state, Order $record): string {
                                            $amount = (float) $state;
                                            if ($amount <= 0) {
                                                return '৳ 0.00';
                                            }
                                            $coupon = $record->promoCode?->code ? " ({$record->promoCode->code})" : '';

                                            return '- ৳ '.number_format($amount, 2).$coupon;
                                        })
                                        ->color(fn ($state): string => (float) $state > 0 ? 'warning' : 'gray'),

                                    TextEntry::make('wallet_amount_paid')
                                        ->label('Paid from Wallet')
                                        ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2))
                                        ->color(fn ($state): string => (float) $state > 0 ? 'info' : 'gray'),

                                    TextEntry::make('total_amount')
                                        ->label('Grand Total Paid')
                                        ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2))
                                        ->size(TextSize::Large)
                                        ->weight(FontWeight::ExtraBold)
                                        ->color('success'),

                                    TextEntry::make('payment_method')
                                        ->label('Payment Method')
                                        ->badge()
                                        ->icon('heroicon-m-credit-card'),

                                    TextEntry::make('payment_status')
                                        ->label('Payment Status')
                                        ->badge(),

                                    TextEntry::make('gateway_transaction_id')
                                        ->label('Gateway Txn ID')
                                        ->fontFamily('mono')
                                        ->copyable()
                                        ->placeholder('N/A (Wallet / Internal)')
                                        ->icon('heroicon-m-receipt-refund'),

                                    TextEntry::make('net_profit')
                                        ->label('Estimated Net Profit')
                                        ->formatStateUsing(function (Order $record): string {
                                            if ($record->cost_price === null) {
                                                return 'N/A (No Cost Set)';
                                            }
                                            $cost = (float) $record->cost_price * $record->quantity;
                                            $profit = (float) $record->total_amount - $cost;
                                            $margin = (float) $record->total_amount > 0 ? round(($profit / (float) $record->total_amount) * 100, 1) : 0;

                                            return '৳ '.number_format($profit, 2)." ({$margin}% margin)";
                                        })
                                        ->weight(FontWeight::Bold)
                                        ->color(function (Order $record): string {
                                            if ($record->cost_price === null) {
                                                return 'gray';
                                            }

                                            return ($record->net_profit ?? 0) >= 0 ? 'success' : 'danger';
                                        })
                                        ->icon('heroicon-m-chart-bar-square'),
                                ]),
                        ]),

                    // Section 3: Service Request Form Data (Visible for service products)
                    Section::make('Service Order Details (Customer Inputs)')
                        ->icon('heroicon-m-wrench-screwdriver')
                        ->description('Input parameters submitted by customer at checkout for manual fulfillment')
                        ->visible(fn (Order $record): bool => ! empty($record->service_data))
                        ->schema([
                            KeyValueEntry::make('service_data')
                                ->label('Customer Form Fields')
                                ->keyLabel('Field Name')
                                ->valueLabel('Submitted Value')
                                ->columnSpanFull(),
                        ]),

                    // Section 4: Audit & Activity Timeline
                    Section::make('Activity & Session Audit')
                        ->icon('heroicon-m-shield-check')
                        ->collapsible()
                        ->schema([
                            Grid::make(4)
                                ->schema([
                                    TextEntry::make('created_at')
                                        ->label('Order Placed At')
                                        ->dateTime('M d, Y h:i A')
                                        ->icon('heroicon-m-calendar'),

                                    TextEntry::make('fulfilled_at')
                                        ->label('Delivered / Fulfilled At')
                                        ->dateTime('M d, Y h:i A')
                                        ->placeholder('Not yet fulfilled')
                                        ->icon('heroicon-m-check-badge'),

                                    TextEntry::make('ip_address')
                                        ->label('Customer IP Address')
                                        ->fontFamily('mono')
                                        ->copyable()
                                        ->placeholder('Unknown')
                                        ->icon('heroicon-m-globe-alt'),

                                    TextEntry::make('user_agent')
                                        ->label('Client Device & Platform')
                                        ->formatStateUsing(function (?string $state): string {
                                            if (empty($state)) {
                                                return 'Unknown Client';
                                            }
                                            $browser = UserAgentParser::getBrowser($state);
                                            $device = UserAgentParser::getDeviceType($state);

                                            return "{$browser} on {$device}";
                                        })
                                        ->badge()
                                        ->color('gray')
                                        ->icon('heroicon-m-computer-desktop'),
                                ]),
                        ]),
                ])->columnSpan(['default' => 1, 'md' => 2, 'lg' => 2]),

                // ── Sidebar Column (1/3 width) ──
                Group::make([
                    // Sidebar 1: Order Lifecycle Statuses
                    Section::make('Order Lifecycle')
                        ->icon('heroicon-m-identification')
                        ->schema([
                            TextEntry::make('order_number')
                                ->label('Order Reference')
                                ->fontFamily('mono')
                                ->size(TextSize::Large)
                                ->weight(FontWeight::Bold)
                                ->copyable()
                                ->copyMessage('Order number copied!')
                                ->icon('heroicon-m-ticket'),

                            TextEntry::make('order_type')
                                ->label('Channel / Tier')
                                ->badge()
                                ->state(fn (Order $record): string => str_starts_with($record->order_number, 'RES-') ? 'Reseller Order (B2B)' : 'Direct Consumer (B2C)')
                                ->color(fn (Order $record): string => str_starts_with($record->order_number, 'RES-') ? 'warning' : 'primary'),

                            Grid::make(1)
                                ->schema([
                                    TextEntry::make('status')
                                        ->label('Order Status')
                                        ->badge(),

                                    TextEntry::make('payment_status')
                                        ->label('Payment Status')
                                        ->badge(),

                                    TextEntry::make('fulfillment_status')
                                        ->label('Fulfillment Status')
                                        ->badge(),
                                ]),
                        ]),

                    // Sidebar 2: Customer Profile Card
                    Section::make('Customer Profile')
                        ->icon('heroicon-m-user-circle')
                        ->schema([
                            TextEntry::make('customer.name')
                                ->label('Customer Name')
                                ->weight(FontWeight::Bold)
                                ->size(TextSize::Medium)
                                ->icon('heroicon-m-user'),

                            TextEntry::make('customer_tier')
                                ->label('Account Tier')
                                ->badge()
                                ->state(fn (Order $record): string => $record->customer?->is_reseller ? 'Reseller ('.($record->customer->reseller_discount ?? 0).'% Off)' : 'Standard Customer')
                                ->color(fn (Order $record): string => $record->customer?->is_reseller ? 'warning' : 'gray'),

                            TextEntry::make('customer.email')
                                ->label('Email Address')
                                ->copyable()
                                ->copyMessage('Email copied!')
                                ->icon('heroicon-m-envelope')
                                ->color('primary')
                                ->url(fn (Order $record): ?string => $record->customer?->email ? 'mailto:'.$record->customer->email : null),

                            TextEntry::make('customer.whatsapp_number')
                                ->label('WhatsApp / Contact')
                                ->placeholder('Not provided')
                                ->icon('heroicon-m-phone')
                                ->url(fn (Order $record): ?string => $record->customer?->whatsapp_number ? 'https://wa.me/'.preg_replace('/[^0-9]/', '', $record->customer->whatsapp_number) : null)
                                ->openUrlInNewTab(),

                            TextEntry::make('customer.balance')
                                ->label('Current Wallet Balance')
                                ->formatStateUsing(fn ($state): string => $state !== null ? '৳ '.number_format((float) $state, 2) : '৳ 0.00')
                                ->weight(FontWeight::Bold)
                                ->color('success')
                                ->icon('heroicon-m-wallet'),

                            TextEntry::make('customer_orders_count')
                                ->label('Lifetime Orders Placed')
                                ->badge()
                                ->color('info')
                                ->state(fn (Order $record): string => $record->customer_id ? Order::where('customer_id', $record->customer_id)->count().' orders' : '1 order')
                                ->icon('heroicon-m-shopping-bag'),

                            TextEntry::make('view_profile')
                                ->label('Customer Record')
                                ->state(fn (): string => 'Open Customer Profile ↗')
                                ->color('primary')
                                ->url(fn (Order $record): ?string => $record->customer_id ? url('/admin/customers/'.$record->customer_id) : null)
                                ->openUrlInNewTab(),
                        ]),

                    // Sidebar 3: Order Notes
                    Section::make('Order Notes & Remarks')
                        ->icon('heroicon-m-chat-bubble-bottom-center-text')
                        ->collapsible()
                        ->schema([
                            TextEntry::make('customer_notes')
                                ->label('Customer Remarks')
                                ->placeholder('No customer remarks recorded.')
                                ->icon('heroicon-m-chat-bubble-oval-left'),

                            TextEntry::make('admin_notes')
                                ->label('Internal / System Notes')
                                ->placeholder('No system notes recorded.')
                                ->icon('heroicon-m-document-text'),
                        ]),
                ])->columnSpan(['default' => 1, 'md' => 1, 'lg' => 1]),
            ])
            ->columns(['default' => 1, 'md' => 3, 'lg' => 3]);
    }
}
