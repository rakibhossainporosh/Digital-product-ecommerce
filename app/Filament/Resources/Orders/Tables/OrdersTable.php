<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderFulfillmentService;
use App\Services\OrderService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $query->with(['customer', 'product', 'productVariant', 'licenseKeys']);

                $filter = request()->query('filter');

                if ($filter === 'paid') {
                    $query->where('payment_status', PaymentStatus::Paid);
                } elseif ($filter === 'pending') {
                    $query->whereIn('status', [OrderStatus::Pending, OrderStatus::Processing]);
                } elseif ($filter === 'fulfilled') {
                    $query->where('fulfillment_status', FulfillmentStatus::Fulfilled);
                }

                return $query;
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('row_index')
                    ->rowIndex()
                    ->label('#')
                    ->toggleable(),

                TextColumn::make('order_number')
                    ->label('Order #')
                    ->weight(FontWeight::Bold)
                    ->fontFamily('mono')
                    ->copyable()
                    ->copyMessage('Order number copied!')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Order $record): ?string => str_starts_with($record->order_number, 'RES-') ? 'Wholesale Reseller Order' : null),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable()
                    ->description(fn (Order $record): ?string => $record->customer?->email),

                TextColumn::make('product.name')
                    ->label('Product & Variant')
                    ->searchable()
                    ->description(fn (Order $record): ?string => $record->productVariant?->duration_name),

                TextColumn::make('quantity')
                    ->label('Qty')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2))
                    ->weight(FontWeight::ExtraBold)
                    ->color('success')
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->sortable(),

                TextColumn::make('fulfillment_status')
                    ->label('Fulfillment')
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Overall Status')
                    ->options(OrderStatus::class)
                    ->native(false),

                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options(PaymentStatus::class)
                    ->native(false),

                SelectFilter::make('fulfillment_status')
                    ->label('Fulfillment Status')
                    ->options(FulfillmentStatus::class)
                    ->native(false),

                SelectFilter::make('payment_method')
                    ->label('Payment Method')
                    ->options(PaymentMethod::class)
                    ->native(false),

                TernaryFilter::make('is_reseller_order')
                    ->label('Channel')
                    ->placeholder('All Channels')
                    ->trueLabel('Reseller Orders Only')
                    ->falseLabel('Retail Orders Only')
                    ->queries(
                        true: fn (Builder $query) => $query->where('order_number', 'like', 'RES-%'),
                        false: fn (Builder $query) => $query->where('order_number', 'like', 'ORD-%'),
                    )
                    ->native(false),

                TrashedFilter::make()->native(false),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),

                    Action::make('retryFulfillment')
                        ->label('Retry Fulfillment')
                        ->icon('heroicon-m-arrow-path')
                        ->color('primary')
                        ->visible(fn (Order $record): bool => $record->canBeFulfilled())
                        ->requiresConfirmation()
                        ->modalHeading('Retry License Key Fulfillment')
                        ->modalDescription('System will attempt to atomically allocate available stock for this order.')
                        ->action(function (Order $record, OrderFulfillmentService $fulfillmentService): void {
                            $result = $fulfillmentService->fulfill($record);

                            if ($result['success']) {
                                Notification::make()
                                    ->title('Fulfillment Successful')
                                    ->body("Delivered {$record->quantity} license key(s) to order #{$record->order_number}")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Fulfillment Failed')
                                    ->body($result['error'] ?? 'Stock allocation failed.')
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Action::make('whatsappCustomer')
                        ->label('WhatsApp Customer')
                        ->icon('heroicon-m-chat-bubble-left-ellipsis')
                        ->color('success')
                        ->visible(fn (Order $record): bool => isset($record->service_data['whatsapp_number']) || ! empty($record->customer->whatsapp))
                        ->url(function (Order $record): string {
                            $number = $record->service_data['whatsapp_number'] ?? $record->customer->whatsapp ?? '';
                            $cleanNumber = preg_replace('/[^0-9]/', '', $number);

                            return "https://wa.me/{$cleanNumber}?text=Hello regarding your order #{$record->order_number}";
                        })
                        ->openUrlInNewTab(),

                    Action::make('refundToWallet')
                        ->label('Refund to Customer Wallet')
                        ->icon('heroicon-m-arrow-uturn-left')
                        ->color('warning')
                        ->visible(fn (Order $record): bool => $record->canBeRefunded())
                        ->modalWidth('md')
                        ->modalHeading(fn (Order $record): string => "Refund Order #{$record->order_number}")
                        ->modalDescription(fn (Order $record): string => 'Amount to be credited back to customer wallet: ৳ '.number_format((float) $record->total_amount, 2))
                        ->form([
                            Textarea::make('reason')
                                ->label('Refund Reason')
                                ->required()
                                ->placeholder('e.g. Customer requested cancellation / defective key replaced')
                                ->rows(3),
                        ])
                        ->action(function (Order $record, array $data, OrderService $orderService): void {
                            /** @var User $admin */
                            $admin = Auth::user();

                            try {
                                $orderService->refundOrderToWallet($record, $data['reason'], $admin);

                                Notification::make()
                                    ->title('Order Refunded to Wallet')
                                    ->body('Successfully refunded ৳ '.number_format((float) $record->total_amount, 2)." to {$record->customer->name}'s wallet.")
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Refund Failed')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                ]),
            ]);
    }
}
