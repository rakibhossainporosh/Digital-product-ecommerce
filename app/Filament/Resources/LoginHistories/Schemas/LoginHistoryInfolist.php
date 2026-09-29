<?php

namespace App\Filament\Resources\LoginHistories\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LoginHistoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Authentication Event Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('email')
                            ->label('Attempted Email')
                            ->icon('heroicon-m-envelope')
                            ->copyable(),

                        TextEntry::make('user.name')
                            ->label('Matched User Account')
                            ->placeholder('Unregistered / Guest Attempt')
                            ->icon('heroicon-m-user'),

                        TextEntry::make('status')
                            ->label('Authentication Result')
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
                            ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                        TextEntry::make('login_at')
                            ->label('Event Timestamp')
                            ->dateTime('M d, Y h:i:s A')
                            ->icon('heroicon-m-clock'),

                        TextEntry::make('failure_reason')
                            ->label('Failure Reason')
                            ->placeholder('None (Login Successful)')
                            ->columnSpan(2),
                    ]),

                Section::make('Client Environment & Security Context')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('ip_address')
                            ->label('Client IP Address')
                            ->fontFamily('mono')
                            ->icon('heroicon-m-globe-alt')
                            ->copyable(),

                        TextEntry::make('device_type')
                            ->label('Device Category')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('browser')
                            ->label('Web Browser'),

                        TextEntry::make('platform')
                            ->label('Operating System'),

                        TextEntry::make('user_agent')
                            ->label('Raw User Agent Header')
                            ->fontFamily('mono')
                            ->copyable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
