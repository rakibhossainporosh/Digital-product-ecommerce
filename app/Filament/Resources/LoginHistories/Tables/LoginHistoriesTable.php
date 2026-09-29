<?php

namespace App\Filament\Resources\LoginHistories\Tables;

use App\Models\LoginHistory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LoginHistoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('login_at', 'desc')
            ->columns([
                TextColumn::make('email')
                    ->label('User / Email')
                    ->description(fn (LoginHistory $record): ?string => $record->user?->name)
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user-circle'),

                TextColumn::make('status')
                    ->label('Result')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        default => 'warning',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'success' => 'heroicon-m-check-circle',
                        'failed' => 'heroicon-m-x-circle',
                        default => 'heroicon-m-exclamation-triangle',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->fontFamily('mono')
                    ->copyable()
                    ->copyMessage('IP Address copied')
                    ->searchable()
                    ->icon('heroicon-m-globe-alt'),

                TextColumn::make('device_type')
                    ->label('Device')
                    ->badge()
                    ->color('gray')
                    ->icon(fn (?string $state): string => match (strtolower((string) $state)) {
                        'mobile' => 'heroicon-m-device-phone-mobile',
                        'tablet' => 'heroicon-m-device-tablet',
                        'desktop' => 'heroicon-m-computer-desktop',
                        'bot' => 'heroicon-m-cpu-chip',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->sortable(),

                TextColumn::make('browser')
                    ->label('Browser')
                    ->sortable(),

                TextColumn::make('platform')
                    ->label('OS / Platform')
                    ->sortable(),

                TextColumn::make('failure_reason')
                    ->label('Failure Reason')
                    ->placeholder('None (Success)')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('login_at')
                    ->label('Timestamp')
                    ->dateTime('M d, Y h:i:s A')
                    ->sortable()
                    ->icon('heroicon-m-clock'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'success' => 'Successful Logins',
                        'failed' => 'Failed Attempts',
                    ]),

                SelectFilter::make('device_type')
                    ->options([
                        'Desktop' => 'Desktop',
                        'Mobile' => 'Mobile',
                        'Tablet' => 'Tablet',
                        'Bot' => 'Bot / Crawler',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false),
                ]),
            ]);
    }
}
