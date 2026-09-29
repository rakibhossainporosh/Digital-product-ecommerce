<?php

namespace App\Filament\Resources\LoginHistories;

use App\Filament\Resources\LoginHistories\Pages\ListLoginHistories;
use App\Filament\Resources\LoginHistories\Pages\ViewLoginHistory;
use App\Filament\Resources\LoginHistories\Schemas\LoginHistoryInfolist;
use App\Filament\Resources\LoginHistories\Tables\LoginHistoriesTable;
use App\Filament\Resources\LoginHistories\Widgets\LoginHistoryStats;
use App\Models\LoginHistory;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LoginHistoryResource extends Resource
{
    protected static ?string $model = LoginHistory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Security & Audit';

    protected static ?string $navigationLabel = 'Audit Logs';

    protected static ?string $pluralModelLabel = 'Audit Logs';

    protected static ?string $modelLabel = 'Audit Log';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user && ($user->hasRole('super_admin') || $user->can('View:LoginHistory') || $user->roles()->exists());
    }

    public static function infolist(Schema $schema): Schema
    {
        return LoginHistoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoginHistoriesTable::configure($table);
    }

    public static function getWidgets(): array
    {
        return [
            LoginHistoryStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoginHistories::route('/'),
            'view' => ViewLoginHistory::route('/{record}'),
        ];
    }
}
