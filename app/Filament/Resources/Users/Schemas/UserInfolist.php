<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profile Overview')
                    ->icon('heroicon-m-user-circle')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Full Name')
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->icon('heroicon-m-user'),

                                TextEntry::make('email')
                                    ->label('Email Address')
                                    ->copyable()
                                    ->copyMessage('Email copied to clipboard')
                                    ->icon('heroicon-m-envelope')
                                    ->color('primary'),

                                TextEntry::make('roles.name')
                                    ->label('Assigned Roles')
                                    ->badge()
                                    ->icon('heroicon-m-shield-check')
                                    ->color(fn (string $state): string => match ($state) {
                                        'super_admin' => 'danger',
                                        'admin' => 'warning',
                                        'manager' => 'info',
                                        default => 'gray',
                                    })
                                    ->placeholder('No roles assigned'),

                                TextEntry::make('status')
                                    ->label('Account Status')
                                    ->badge()
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Suspended / Inactive')
                                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                                    ->icon(fn (bool $state): string => $state ? 'heroicon-m-check-badge' : 'heroicon-m-x-circle'),
                            ]),
                    ]),

                Grid::make(2)
                    ->schema([
                        Section::make('Security & Verification')
                            ->icon('heroicon-m-lock-closed')
                            ->schema([
                                TextEntry::make('email_verified_at')
                                    ->label('Email Status')
                                    ->badge()
                                    ->formatStateUsing(fn ($state): string => $state ? 'Verified' : 'Unverified')
                                    ->color(fn ($state): string => $state ? 'success' : 'warning')
                                    ->icon(fn ($state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                                    ->helperText(fn ($state) => $state ? $state->format('M d, Y · h:i A') : 'Email has not been verified yet.'),

                                TextEntry::make('id')
                                    ->label('System User ID')
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-m-finger-print'),
                            ]),

                        Section::make('Audit Trail & Timeline')
                            ->icon('heroicon-m-clock')
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Registered At')
                                    ->dateTime('M d, Y · h:i A')
                                    ->icon('heroicon-m-calendar-days')
                                    ->helperText(fn (User $record): ?string => $record->created_at?->diffForHumans()),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('M d, Y · h:i A')
                                    ->icon('heroicon-m-arrow-path')
                                    ->helperText(fn (User $record): ?string => $record->updated_at?->diffForHumans()),
                            ]),
                    ]),
            ]);
    }
}
