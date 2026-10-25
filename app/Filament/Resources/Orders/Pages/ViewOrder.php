<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Services\OrderFulfillmentService;
use App\Services\OrderService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 1. Mark as Paid (Manual Gateway / Cash / Offline verification)
            Action::make('markAsPaid')
                ->label('Mark as Paid')
                ->icon('heroicon-m-banknotes')
                ->color('success')
                ->visible(fn (): bool => $this->getRecord()->payment_status === PaymentStatus::Pending && $this->getRecord()->status !== OrderStatus::Cancelled)
                ->modalHeading(fn (): string => "Mark Order #{$this->getRecord()->order_number} as Paid")
                ->modalDescription('Record manual payment receipt and optionally allocate license keys immediately.')
                ->form([
                    TextInput::make('gateway_transaction_id')
                        ->label('Transaction ID / Reference (Optional)')
                        ->placeholder('e.g. bKash TrxID or Bank Slip ID'),
                    Toggle::make('auto_fulfill')
                        ->label('Auto-fulfill order immediately (if stock available)')
                        ->default(true),
                ])
                ->action(function (array $data, OrderFulfillmentService $fulfillmentService): void {
                    /** @var Order $record */
                    $record = $this->getRecord();

                    $updateData = [
                        'payment_status' => PaymentStatus::Paid,
                        'gateway_amount_paid' => $record->total_amount,
                        'status' => OrderStatus::Processing,
                    ];

                    if (! empty($data['gateway_transaction_id'])) {
                        $updateData['gateway_transaction_id'] = $data['gateway_transaction_id'];
                    }

                    $record->update($updateData);

                    if (! empty($data['auto_fulfill']) && ! $record->isService()) {
                        $result = $fulfillmentService->fulfill($record);

                        if ($result['success']) {
                            Notification::make()
                                ->title('Payment Verified & Order Fulfilled')
                                ->body("Payment confirmed and {$record->quantity} license key(s) delivered to order #{$record->order_number}.")
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Payment Verified (Stock Notice)')
                            ->body('Payment recorded, but license stock was insufficient: '.($result['error'] ?? 'Stock out').'. Please restock keys and retry fulfillment.')
                            ->warning()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Payment Verified')
                        ->body("Order #{$record->order_number} has been marked as paid.")
                        ->success()
                        ->send();
                }),

            // 2. Complete Manual Service Order
            Action::make('completeService')
                ->label('Complete Service')
                ->icon('heroicon-m-check-badge')
                ->color('success')
                ->visible(fn (): bool => $this->getRecord()->canBeFulfilled() && $this->getRecord()->isService())
                ->modalHeading(fn (): string => "Complete Service: #{$this->getRecord()->order_number}")
                ->modalDescription('Mark this manual service as fulfilled and completed for the customer.')
                ->modalSubmitActionLabel('Mark as Fulfilled')
                ->form([
                    Textarea::make('completion_notes')
                        ->label('Completion Notes (Optional)')
                        ->placeholder('e.g. In-game Diamonds injected successfully via UID. Verified with player.')
                        ->rows(3),
                ])
                ->action(function (array $data, OrderFulfillmentService $fulfillmentService): void {
                    /** @var Order $record */
                    $record = $this->getRecord();
                    $result = $fulfillmentService->fulfillServiceOrder($record, $data['completion_notes'] ?? null);

                    if ($result['success']) {
                        Notification::make()
                            ->title('Service Order Completed')
                            ->body("Service order #{$record->order_number} has been marked as fulfilled and completed.")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Service Completion Failed')
                            ->body($result['error'] ?? 'Could not mark service order as completed.')
                            ->danger()
                            ->send();
                    }
                }),

            // 3. Retry License Key Fulfillment
            Action::make('retryFulfillment')
                ->label('Retry Fulfillment')
                ->icon('heroicon-m-arrow-path')
                ->color('primary')
                ->visible(fn (): bool => $this->getRecord()->canBeFulfilled() && ! $this->getRecord()->isService())
                ->requiresConfirmation()
                ->modalHeading('Retry License Key Fulfillment')
                ->modalDescription('System will attempt to atomically allocate available stock for this order.')
                ->action(function (OrderFulfillmentService $fulfillmentService): void {
                    /** @var Order $record */
                    $record = $this->getRecord();
                    $result = $fulfillmentService->fulfill($record);

                    if ($result['success']) {
                        Notification::make()
                            ->title('License Keys Delivered')
                            ->body("Successfully allocated and delivered {$record->quantity} license key(s) to order #{$record->order_number}.")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Key Fulfillment Failed')
                            ->body($result['error'] ?? 'Insufficient available keys in stock for this variant. Please restock keys first.')
                            ->danger()
                            ->send();
                    }
                }),

            // 4. Refund to Customer Wallet
            Action::make('refundToWallet')
                ->label('Refund to Wallet')
                ->icon('heroicon-m-arrow-uturn-left')
                ->color('danger')
                ->visible(fn (): bool => $this->getRecord()->canBeRefunded())
                ->modalHeading(fn (): string => "Refund Order #{$this->getRecord()->order_number}")
                ->modalDescription(fn (): string => 'This will credit ৳ '.number_format((float) $this->getRecord()->total_amount, 2)." back to the customer's wallet balance, revoke delivered license keys, and restore coupon quota.")
                ->form([
                    Textarea::make('reason')
                        ->label('Reason for Refund')
                        ->placeholder('e.g. Customer requested refund / Out of stock / Duplicate order')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data, OrderService $orderService): void {
                    /** @var Order $record */
                    $record = $this->getRecord();

                    try {
                        $orderService->refundOrderToWallet($record, $data['reason'], auth()->user());

                        Notification::make()
                            ->title('Order Refunded to Wallet')
                            ->body('Successfully credited ৳ '.number_format((float) $record->total_amount, 2).' back to customer wallet.')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Refund Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // 5. Cancel Unpaid Order
            Action::make('cancelOrder')
                ->label('Cancel Order')
                ->icon('heroicon-m-x-circle')
                ->color('gray')
                ->visible(fn (): bool => $this->getRecord()->status === OrderStatus::Pending && $this->getRecord()->payment_status !== PaymentStatus::Paid)
                ->requiresConfirmation()
                ->modalHeading('Cancel Unpaid Order')
                ->modalDescription('Are you sure you want to cancel this unpaid order? Customer will no longer be able to complete payment.')
                ->action(function (): void {
                    /** @var Order $record */
                    $record = $this->getRecord();
                    $record->update(['status' => OrderStatus::Cancelled]);

                    Notification::make()
                        ->title('Order Cancelled')
                        ->body("Order #{$record->order_number} has been marked as cancelled.")
                        ->warning()
                        ->send();
                }),

            // 6. Direct WhatsApp Chat with Customer
            Action::make('whatsappCustomer')
                ->label('WhatsApp Customer')
                ->icon('heroicon-m-chat-bubble-left-ellipsis')
                ->color('success')
                ->visible(fn (): bool => ! empty($this->getRecord()->service_data['whatsapp_number']) || ! empty($this->getRecord()->customer?->whatsapp_number))
                ->url(function (): string {
                    /** @var Order $record */
                    $record = $this->getRecord();
                    $number = $record->service_data['whatsapp_number'] ?? $record->customer?->whatsapp_number ?? '';
                    $cleanNumber = preg_replace('/[^0-9]/', '', (string) $number);

                    return "https://wa.me/{$cleanNumber}?text=".urlencode("Hello {$record->customer?->name}, regarding your order #{$record->order_number} on ".setting('app_name', 'our store'));
                })
                ->openUrlInNewTab(),
        ];
    }
}
