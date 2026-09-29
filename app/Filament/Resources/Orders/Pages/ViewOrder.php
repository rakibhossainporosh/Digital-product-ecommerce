<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Services\OrderFulfillmentService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
                        ->placeholder('e.g. Device rooted successfully. Handed over credentials via WhatsApp.')
                        ->rows(3),
                ])
                ->action(function (array $data, OrderFulfillmentService $fulfillmentService): void {
                    /** @var Order $record */
                    $record = $this->getRecord();
                    $result = $fulfillmentService->fulfillServiceOrder($record, $data['completion_notes'] ?? null);

                    if ($result['success']) {
                        Notification::make()
                            ->title('Service Fulfilled')
                            ->body("Service order #{$record->order_number} marked as fulfilled.")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Fulfillment Failed')
                            ->body($result['error'] ?? 'Could not fulfill service order.')
                            ->danger()
                            ->send();
                    }
                }),

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
                ->visible(fn (): bool => isset($this->getRecord()->service_data['whatsapp_number']) || ! empty($this->getRecord()->customer?->whatsapp))
                ->url(function (): string {
                    /** @var Order $record */
                    $record = $this->getRecord();
                    $number = $record->service_data['whatsapp_number'] ?? $record->customer?->whatsapp ?? '';
                    $cleanNumber = preg_replace('/[^0-9]/', '', (string) $number);

                    return "https://wa.me/{$cleanNumber}?text=Hello regarding your order #{$record->order_number}";
                })
                ->openUrlInNewTab(),
        ];
    }
}
