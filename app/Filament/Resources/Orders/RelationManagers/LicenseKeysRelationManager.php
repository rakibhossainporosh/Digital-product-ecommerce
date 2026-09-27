<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\LicenseKey;
use App\Services\LicenseKeyStockService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LicenseKeysRelationManager extends RelationManager
{
    protected static string $relationship = 'licenseKeys';

    protected static ?string $title = 'Delivered License Keys';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-key';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('key')
            ->columns([
                TextColumn::make('masked_key')
                    ->label('License Key')
                    ->fontFamily('mono')
                    ->weight(FontWeight::Bold)
                    ->copyable()
                    ->copyableState(fn (LicenseKey $record): string => $record->key)
                    ->copyMessage('Decrypted license key copied to clipboard!')
                    ->tooltip('Click icon to copy decrypted key')
                    ->searchable(false),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('batch_ref')
                    ->label('Batch Reference')
                    ->badge()
                    ->color('gray')
                    ->placeholder('Manual'),

                TextColumn::make('sold_at')
                    ->label('Delivered At')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->actions([
                Action::make('revokeKey')
                    ->label('Revoke Key')
                    ->icon('heroicon-m-no-symbol')
                    ->color('danger')
                    ->visible(fn (LicenseKey $record): bool => $record->isSold())
                    ->modalWidth('md')
                    ->modalHeading('Revoke Delivered License Key')
                    ->modalDescription('Revoking this key marks it as invalid and records an audit log.')
                    ->form([
                        Textarea::make('reason')
                            ->label('Revocation Reason')
                            ->required()
                            ->placeholder('e.g. Customer reported defective / refunded'),
                    ])
                    ->action(function (LicenseKey $record, array $data, LicenseKeyStockService $stockService): void {
                        $stockService->revokeKey($record, $data['reason']);

                        Notification::make()
                            ->title('License Key Revoked')
                            ->body('Key has been marked as revoked.')
                            ->warning()
                            ->send();
                    }),
            ]);
    }
}
