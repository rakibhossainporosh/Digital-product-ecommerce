<x-filament-panels::page>
    @php
        $data = $this->getViewData();
    @endphp

    {{-- Filter Section --}}
    <x-filament::section>
        <x-slot name="heading">
            <div style="display: flex; align-items: center; gap: 8px;">
                <x-heroicon-m-funnel style="width: 18px; height: 18px;" />
                <span>Filters</span>
                @if($data['selectedProduct'])
                    <x-filament::badge color="info" size="sm">
                        {{ $data['selectedProduct']->name }}
                    </x-filament::badge>
                @endif
                @if($data['selectedVariant'])
                    <x-filament::badge color="warning" size="sm">
                        {{ $data['selectedVariant']->duration_name }}
                    </x-filament::badge>
                @endif
            </div>
        </x-slot>

        <form wire:submit.prevent="applyFilters">
            <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; align-items: end;">
                {{-- Start Date --}}
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; color: #9ca3af;">Start Date</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="date" wire:model.defer="startDate" />
                    </x-filament::input.wrapper>
                </div>

                {{-- End Date --}}
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; color: #9ca3af;">End Date</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="date" wire:model.defer="endDate" />
                    </x-filament::input.wrapper>
                </div>

                {{-- Product Filter --}}
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; color: #9ca3af;">Product</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="productId">
                            <option value="">All Products</option>
                            @foreach($data['products'] as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                {{-- Variant Filter --}}
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; color: #9ca3af;">Duration / Variant</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="variantId">
                            <option value="">All Variants</option>
                            @foreach($this->availableVariants as $v)
                                <option value="{{ $v->id }}">
                                    {{ $productId ? $v->duration_name : ($v->product?->name . ' — ' . $v->duration_name) }}
                                </option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                {{-- Buttons --}}
                <div style="display: flex; gap: 8px;">
                    <x-filament::button type="submit" icon="heroicon-m-funnel">
                        Filter
                    </x-filament::button>
                    <x-filament::button color="gray" wire:click="resetFilters" type="button" icon="heroicon-m-arrow-path">
                        Reset
                    </x-filament::button>
                </div>
            </div>
        </form>
    </x-filament::section>

    {{-- Summary info --}}
    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: #9ca3af; padding: 0 4px;">
        <div>
            @if($data['selectedVariant'])
                Performance for <strong style="color: #f59e0b;">{{ $data['selectedProduct']?->name }}</strong> ({{ $data['selectedVariant']->duration_name }}) — Direct Online vs. Wallet.
            @elseif($data['selectedProduct'])
                Performance for <strong style="color: #f59e0b;">{{ $data['selectedProduct']->name }}</strong> — Direct Online vs. Wallet.
            @else
                Complete overview: Direct Online Gateway, Wallet Purchases & Wallet Deposits.
            @endif
        </div>
        <span style="font-family: monospace;">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} → {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
    </div>

    {{-- Stat Cards --}}
    @if($data['selectedProduct'])
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
            {{-- Total Product Sales --}}
            <x-filament::section>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #10b981;">
                        {{ $data['selectedVariant'] ? 'Total Variant Sales' : 'Total Product Sales' }}
                    </span>
                    <x-filament::badge color="success" size="sm">{{ $data['totalOrdersCount'] }} Orders</x-filament::badge>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #10b981;">৳ {{ number_format($data['totalProductSales'], 2) }}</div>
                <div style="margin-top: 6px; font-size: 12px; color: #9ca3af;">
                    <span style="color: #3b82f6; font-weight: 500;">৳ {{ number_format($data['totalDirectSales'], 2) }} Online</span>
                    +
                    <span style="color: #f59e0b; font-weight: 500;">৳ {{ number_format($data['totalWalletSales'], 2) }} Wallet</span>
                </div>
            </x-filament::section>

            {{-- Direct Online Sales --}}
            <x-filament::section>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #3b82f6;">Direct Online Sales</span>
                    <x-filament::badge color="info" size="sm">{{ $data['directOrdersCount'] }} Orders</x-filament::badge>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #3b82f6;">৳ {{ number_format($data['totalDirectSales'], 2) }}</div>
                <div style="margin-top: 6px; font-size: 11px; color: #9ca3af;">Paid via Payment Gateways</div>
            </x-filament::section>

            {{-- Wallet Sales --}}
            <x-filament::section>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #f59e0b;">Wallet Sales</span>
                    <x-filament::badge color="warning" size="sm">{{ $data['walletOrdersCount'] }} Orders</x-filament::badge>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #f59e0b;">৳ {{ number_format($data['totalWalletSales'], 2) }}</div>
                <div style="margin-top: 6px; font-size: 11px; color: #9ca3af;">Paid from Customer / Reseller Wallet</div>
            </x-filament::section>
        </div>
    @else
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
            {{-- Real Cash Revenue --}}
            <x-filament::section>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #10b981;">Real Cash Revenue</span>
                    <x-filament::badge color="success" size="sm">Inflow</x-filament::badge>
                </div>
                <div style="font-size: 24px; font-weight: 800; color: #10b981;">৳ {{ number_format($data['totalRevenue'], 2) }}</div>
                <div style="margin-top: 6px; font-size: 11px; color: #9ca3af;">Online Sales + Wallet Deposits</div>
            </x-filament::section>

            {{-- Direct Online Sales --}}
            <x-filament::section>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #3b82f6;">Direct Online Sales</span>
                    <x-filament::badge color="info" size="sm">{{ $data['directOrdersCount'] }} Orders</x-filament::badge>
                </div>
                <div style="font-size: 24px; font-weight: 800; color: #3b82f6;">৳ {{ number_format($data['totalDirectSales'], 2) }}</div>
                <div style="margin-top: 6px; font-size: 11px; color: #9ca3af;">Paid via Gateways</div>
            </x-filament::section>

            {{-- Wallet Sales --}}
            <x-filament::section>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #f59e0b;">Wallet Sales</span>
                    <x-filament::badge color="warning" size="sm">{{ $data['walletOrdersCount'] }} Orders</x-filament::badge>
                </div>
                <div style="font-size: 24px; font-weight: 800; color: #f59e0b;">৳ {{ number_format($data['totalWalletSales'], 2) }}</div>
                <div style="margin-top: 6px; font-size: 11px; color: #9ca3af;">Purchases via Wallet Balance</div>
            </x-filament::section>

            {{-- Wallet Deposits --}}
            <x-filament::section>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8b5cf6;">Wallet Deposits</span>
                    <x-filament::badge color="gray" size="sm">{{ $data['walletDepositsCount'] }} Txns</x-filament::badge>
                </div>
                <div style="font-size: 24px; font-weight: 800; color: #8b5cf6;">৳ {{ number_format($data['totalWalletDeposits'], 2) }}</div>
                <div style="margin-top: 6px; font-size: 11px; color: #9ca3af;">Cash funded into wallets</div>
            </x-filament::section>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{--  Side-by-Side Dual Tables                                     --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <style>
        .sr-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px; }
        .sr-table thead th {
            padding: 12px 16px;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            border-bottom: 2px solid rgba(148,163,184,0.15);
            position: sticky;
            top: 0;
            z-index: 1;
        }
        .sr-table thead th.text-right { text-align: right; }
        .sr-table tbody tr { transition: background-color 0.15s ease; }
        .sr-table tbody tr:hover { background-color: rgba(148,163,184,0.06); }
        .sr-table tbody tr:nth-child(even) { background-color: rgba(148,163,184,0.03); }
        .sr-table tbody td { padding: 12px 16px; vertical-align: middle; border-bottom: 1px solid rgba(148,163,184,0.08); }
        .sr-table tbody td.text-right { text-align: right; }
        .sr-table .sr-date { font-size: 12px; color: #94a3b8; white-space: nowrap; }
        .sr-table .sr-order-num { font-family: ui-monospace, SFMono-Regular, monospace; font-size: 12px; font-weight: 600; }
        .sr-table .sr-order-num.blue { color: #3b82f6; }
        .sr-table .sr-order-num.amber { color: #f59e0b; }
        .sr-table .sr-variant { font-size: 10px; color: #94a3b8; margin-top: 2px; }
        .sr-table .sr-product { font-size: 12px; font-weight: 500; max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .sr-table .sr-customer-name { font-size: 12px; font-weight: 500; }
        .sr-table .sr-customer-email { font-size: 10px; color: #94a3b8; margin-top: 1px; max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .sr-table .sr-amount { font-family: ui-monospace, SFMono-Regular, monospace; font-size: 13px; font-weight: 700; white-space: nowrap; }
        .sr-table .sr-amount.blue { color: #3b82f6; }
        .sr-table .sr-amount.amber { color: #f59e0b; }
        .sr-table .sr-amount.purple { color: #8b5cf6; }
        .sr-table .sr-amount.red { color: #ef4444; }
        .sr-table .sr-empty { padding: 48px 16px !important; text-align: center; }
        .sr-table .sr-empty-icon { font-size: 32px; margin-bottom: 8px; opacity: 0.25; }
        .sr-table .sr-empty-text { font-size: 13px; color: #94a3b8; }
        .sr-table .sr-txn-desc { font-size: 10px; color: #94a3b8; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 4px; }
        .sr-pagination { margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(148,163,184,0.1); }
    </style>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start;">

        {{-- ── LEFT: Direct Online Sales Table ── --}}
        <x-filament::section>
            <x-slot name="heading">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <x-heroicon-m-globe-alt style="width: 20px; height: 20px; color: #3b82f6;" />
                    <span>Direct Online Sales</span>
                    <x-filament::badge color="info" size="sm">{{ $data['directOrdersCount'] }}</x-filament::badge>
                </div>
            </x-slot>
            <x-slot name="description">
                Total: <strong style="color: #3b82f6;">৳ {{ number_format($data['totalDirectSales'], 2) }}</strong> from {{ $data['directOrdersCount'] }} completed orders
            </x-slot>

            <div style="overflow-x: auto; margin: -16px; margin-top: 0;">
                <table class="sr-table">
                    <thead>
                        <tr>
                            <th style="width: 115px;">Date</th>
                            <th style="min-width: 140px;">Order #</th>
                            @if(!$data['selectedProduct'])
                                <th style="min-width: 140px;">Product</th>
                            @endif
                            <th style="min-width: 150px;">Customer</th>
                            <th style="width: 110px;">Paid Via</th>
                            <th class="text-right" style="width: 110px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['directOrders'] as $order)
                            <tr>
                                <td>
                                    <span class="sr-date">{{ $order->created_at->format('d M Y') }}</span>
                                    <div style="font-size: 10px; color: #64748b;">{{ $order->created_at->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <div class="sr-order-num blue">#{{ $order->order_number }}</div>
                                    @if($order->productVariant?->duration_name)
                                        <div class="sr-variant">{{ $order->productVariant->duration_name }}</div>
                                    @endif
                                </td>
                                @if(!$data['selectedProduct'])
                                    <td>
                                        <div class="sr-product" title="{{ $order->product?->name }}">{{ $order->product?->name ?? 'Deleted Product' }}</div>
                                    </td>
                                @endif
                                <td>
                                    <div class="sr-customer-name">{{ $order->customer?->name ?? 'Guest' }}</div>
                                    @if($order->customer?->email)
                                        <div class="sr-customer-email" title="{{ $order->customer->email }}">{{ $order->customer->email }}</div>
                                    @endif
                                </td>
                                <td>
                                    <x-filament::badge color="info" size="sm">{{ $order->payment_method?->getLabel() ?? 'Gateway' }}</x-filament::badge>
                                </td>
                                <td class="text-right">
                                    <span class="sr-amount blue">৳ {{ number_format($order->gateway_amount_paid > 0 ? $order->gateway_amount_paid : $order->total_amount, 2) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $data['selectedProduct'] ? 5 : 6 }}" class="sr-empty">
                                    <div class="sr-empty-icon">🛒</div>
                                    <div class="sr-empty-text">No direct online sales found in this period.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($data['directOrders']->hasPages())
                <div class="sr-pagination">
                    {{ $data['directOrders']->links() }}
                </div>
            @endif
        </x-filament::section>

        {{-- ── RIGHT: Wallet Sales / Deposits Table ── --}}
        <x-filament::section>
            <x-slot name="heading">
                @if($data['selectedProduct'])
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <x-heroicon-m-wallet style="width: 20px; height: 20px; color: #f59e0b;" />
                        <span>Wallet Sales</span>
                        <x-filament::badge color="warning" size="sm">{{ $data['walletOrdersCount'] }}</x-filament::badge>
                    </div>
                @else
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <x-filament::button
                            :color="$activeRightTab === 'sales' ? 'warning' : 'gray'"
                            size="xs"
                            wire:click="setRightTab('sales')"
                            icon="heroicon-m-wallet"
                        >
                            Wallet Sales ({{ $data['walletOrdersCount'] }})
                        </x-filament::button>
                        <x-filament::button
                            :color="$activeRightTab === 'deposits' ? 'success' : 'gray'"
                            size="xs"
                            wire:click="setRightTab('deposits')"
                            icon="heroicon-m-banknotes"
                        >
                            Wallet Deposits ({{ $data['walletDepositsCount'] }})
                        </x-filament::button>
                    </div>
                @endif
            </x-slot>
            <x-slot name="description">
                @if($data['selectedProduct'] || $activeRightTab === 'sales')
                    Total: <strong style="color: #f59e0b;">৳ {{ number_format($data['totalWalletSales'], 2) }}</strong> from {{ $data['walletOrdersCount'] }} wallet orders
                @else
                    Total: <strong style="color: #8b5cf6;">৳ {{ number_format($data['totalWalletDeposits'], 2) }}</strong> from {{ $data['walletDepositsCount'] }} transactions
                @endif
            </x-slot>

            @if($data['selectedProduct'] || $activeRightTab === 'sales')
                {{-- ── Wallet Sales Table ── --}}
                <div style="overflow-x: auto; margin: -16px; margin-top: 0;">
                    <table class="sr-table">
                        <thead>
                            <tr>
                                <th style="width: 115px;">Date</th>
                                <th style="min-width: 140px;">Order #</th>
                                @if(!$data['selectedProduct'])
                                    <th style="min-width: 140px;">Product</th>
                                @endif
                                <th style="min-width: 150px;">Customer</th>
                                <th style="width: 110px;">Method</th>
                                <th class="text-right" style="width: 110px;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['walletOrders'] as $order)
                                <tr>
                                    <td>
                                        <span class="sr-date">{{ $order->created_at->format('d M Y') }}</span>
                                        <div style="font-size: 10px; color: #64748b;">{{ $order->created_at->format('h:i A') }}</div>
                                    </td>
                                    <td>
                                        <div class="sr-order-num amber">#{{ $order->order_number }}</div>
                                        @if($order->productVariant?->duration_name)
                                            <div class="sr-variant">{{ $order->productVariant->duration_name }}</div>
                                        @endif
                                    </td>
                                    @if(!$data['selectedProduct'])
                                        <td>
                                            <div class="sr-product" title="{{ $order->product?->name }}">{{ $order->product?->name ?? 'Deleted Product' }}</div>
                                        </td>
                                    @endif
                                    <td>
                                        <div class="sr-customer-name">{{ $order->customer?->name ?? 'Guest' }}</div>
                                        @if($order->customer?->email)
                                            <div class="sr-customer-email" title="{{ $order->customer->email }}">{{ $order->customer->email }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <x-filament::badge color="warning" size="sm">
                                            {{ $order->wallet_amount_paid > 0 && $order->gateway_amount_paid > 0 ? 'Wallet Portion' : 'Wallet Balance' }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="text-right">
                                        <span class="sr-amount amber">৳ {{ number_format($order->wallet_amount_paid > 0 ? $order->wallet_amount_paid : $order->total_amount, 2) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $data['selectedProduct'] ? 5 : 6 }}" class="sr-empty">
                                        <div class="sr-empty-icon">💳</div>
                                        <div class="sr-empty-text">No wallet sales found in this period.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($data['walletOrders']->hasPages())
                    <div class="sr-pagination">
                        {{ $data['walletOrders']->links() }}
                    </div>
                @endif
            @elseif($data['walletTransactions'])
                {{-- ── Wallet Deposits Table ── --}}
                <div style="overflow-x: auto; margin: -16px; margin-top: 0;">
                    <table class="sr-table">
                        <thead>
                            <tr>
                                <th style="width: 115px;">Date</th>
                                <th style="min-width: 160px;">Customer</th>
                                <th style="min-width: 180px;">Type / Description</th>
                                <th class="text-right" style="width: 120px;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['walletTransactions'] as $txn)
                                <tr>
                                    <td>
                                        <span class="sr-date">{{ $txn->created_at->format('d M Y') }}</span>
                                        <div style="font-size: 10px; color: #64748b;">{{ $txn->created_at->format('h:i A') }}</div>
                                    </td>
                                    <td>
                                        <div class="sr-customer-name">{{ $txn->customer?->name ?? 'N/A' }}</div>
                                        @if($txn->customer?->email)
                                            <div class="sr-customer-email" title="{{ $txn->customer->email }}">{{ $txn->customer->email }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($txn->type === \App\Enums\WalletTransactionType::AdminAdjust)
                                            @if($txn->direction === \App\Enums\TransactionDirection::Debit)
                                                <x-filament::badge color="danger" size="sm">Admin Deduct</x-filament::badge>
                                            @else
                                                <x-filament::badge color="success" size="sm">Admin Add</x-filament::badge>
                                            @endif
                                        @else
                                            <x-filament::badge color="info" size="sm">Deposit</x-filament::badge>
                                        @endif
                                        @if($txn->description)
                                            <div class="sr-txn-desc" title="{{ $txn->description }}">{{ $txn->description }}</div>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <span class="sr-amount {{ $txn->direction === \App\Enums\TransactionDirection::Debit ? 'red' : 'purple' }}">
                                            {{ $txn->direction === \App\Enums\TransactionDirection::Debit ? '-' : '+' }}৳ {{ number_format(abs((float) $txn->amount), 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="sr-empty">
                                        <div class="sr-empty-icon">🏦</div>
                                        <div class="sr-empty-text">No wallet deposits found in this period.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($data['walletTransactions']->hasPages())
                    <div class="sr-pagination">
                        {{ $data['walletTransactions']->links() }}
                    </div>
                @endif
            @endif
        </x-filament::section>
    </div>

</x-filament-panels::page>
