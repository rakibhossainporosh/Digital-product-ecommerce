<?php

namespace App\Filament\Resources\LicenseKeys\Pages;

use App\Filament\Resources\LicenseKeys\LicenseKeyResource;
use App\Filament\Resources\LicenseKeys\Widgets\LicenseKeyStatsOverview;
use App\Models\ProductVariant;
use App\Services\LicenseKeyImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;

class ListLicenseKeys extends ListRecords
{
    protected static string $resource = LicenseKeyResource::class;

    #[Url]
    public ?string $filter = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bulk_import')
                ->label('Bulk Import Keys')
                ->icon('heroicon-m-arrow-up-tray')
                ->color('primary')
                ->modalHeading('Bulk Import License Keys')
                ->modalDescription('Paste multiple keys at once. Metadata, bracket notes, dates, and duplicates will be handled automatically.')
                ->modalWidth('2xl')
                ->form([
                    Select::make('product_variant_id')
                        ->label('Target Product & Duration Tier')
                        ->options(function () {
                            return ProductVariant::with('product')
                                ->get()
                                ->mapWithKeys(function (ProductVariant $variant) {
                                    $label = ($variant->product?->name ?? 'Product').' — '.$variant->duration_name.' ('.$variant->formatted_regular_price.')';

                                    return [$variant->id => $label];
                                });
                        })
                        ->searchable()
                        ->preload()
                        ->required()
                        ->prefixIcon('heroicon-m-tag'),

                    Textarea::make('raw_keys')
                        ->label('License Keys (One per line)')
                        ->placeholder("Paste raw keys here...\nVIP-KEY-1111-2222\nVIP-KEY-3333-4444 [Expires: 2026-12-31]\nVIP-KEY-5555-6666 (Supplier: John)")
                        ->rows(10)
                        ->required()
                        ->helperText('Paste as many keys as needed. Empty lines and duplicate keys are filtered automatically.')
                        ->columnSpanFull(),

                    TextInput::make('batch_ref')
                        ->label('Batch Reference / Supplier Code')
                        ->placeholder('e.g. BATCH-OCT-001 (optional, auto-generated if blank)')
                        ->prefixIcon('heroicon-m-hashtag'),

                    TextInput::make('notes')
                        ->label('Supplier / Import Notes')
                        ->placeholder('e.g. Purchased from Supplier X wholesale')
                        ->prefixIcon('heroicon-m-document-text'),
                ])
                ->action(function (array $data, LicenseKeyImportService $importService): void {
                    $result = $importService->import(
                        variantId: (int) $data['product_variant_id'],
                        rawText: (string) $data['raw_keys'],
                        batchRef: ! empty($data['batch_ref']) ? (string) $data['batch_ref'] : null,
                        notes: ! empty($data['notes']) ? (string) $data['notes'] : null,
                        userId: Auth::id()
                    );

                    $imported = $result['imported'];
                    $skippedBatch = $result['duplicates_in_batch'];
                    $skippedDb = $result['duplicates_in_db'];

                    if ($imported > 0) {
                        Notification::make()
                            ->title("Successfully imported {$imported} keys!")
                            ->body("Batch: {$result['batch_ref']} · Skipped {$skippedBatch} duplicate lines and {$skippedDb} already in database.")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('No new keys imported')
                            ->body("All {$result['total_lines']} keys were duplicates or invalid.")
                            ->warning()
                            ->send();
                    }
                }),

            CreateAction::make()
                ->label('Add Single Key')
                ->icon('heroicon-m-plus')
                ->modalHeading('Create License Key')
                ->modalWidth('xl'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            LicenseKeyStatsOverview::class,
        ];
    }
}
