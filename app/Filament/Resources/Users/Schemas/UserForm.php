<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Credentials')
                    ->description('Personal identification and login authentication details.')
                    ->icon('heroicon-m-user')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Full Name')
                                    ->placeholder('e.g. John Doe')
                                    ->prefixIcon('heroicon-m-user')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label('Email Address')
                                    ->placeholder('e.g. john@example.com')
                                    ->prefixIcon('heroicon-m-envelope')
                                    ->required()
                                    ->email()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),

                                TextInput::make('password')
                                    ->label('Password')
                                    ->placeholder('Enter secure password (min 8 characters)')
                                    ->prefixIcon('heroicon-m-lock-closed')
                                    ->password()
                                    ->revealable()
                                    ->dehydrated(fn (?string $state): bool => filled($state))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->minLength(8)
                                    ->maxLength(255)
                                    ->helperText(fn (string $operation): ?string => $operation === 'edit' ? 'Leave empty to preserve existing password.' : 'Must contain at least 8 characters.')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Access & Account Control')
                    ->description('Assign dynamic roles, verify email, and manage account state.')
                    ->icon('heroicon-m-shield-check')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('roles')
                                    ->label('Assigned Roles')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->prefixIcon('heroicon-m-key')
                                    ->helperText('Select one or more dynamic roles. Permissions are inherited automatically.')
                                    ->columnSpanFull(),

                                DateTimePicker::make('email_verified_at')
                                    ->label('Email Verified At')
                                    ->prefixIcon('heroicon-m-calendar-days')
                                    ->native(false)
                                    ->seconds(false)
                                    ->helperText('Optional: Manually record email verification timestamp.'),

                                Toggle::make('status')
                                    ->label('Active Status')
                                    ->default(true)
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->onIcon('heroicon-m-check')
                                    ->offIcon('heroicon-m-x-mark')
                                    ->inline(false)
                                    ->helperText('Inactive users are immediately barred from logging in.'),
                            ]),
                    ]),
            ]);
    }
}
