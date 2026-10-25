<?php

namespace App\Filament\Resources\LicenseKeys;

use App\Enums\LicenseKeyStatus;
use App\Filament\Resources\LicenseKeys\Pages\ListLicenseKeys;
use App\Filament\Resources\LicenseKeys\Schemas\LicenseKeyForm;
use App\Filament\Resources\LicenseKeys\Tables\LicenseKeysTable;
use App\Filament\Resources\LicenseKeys\Widgets\LicenseKeyStatsOverview;
use App\Models\LicenseKey;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LicenseKeyResource extends Resource
{
    protected static ?string $model = LicenseKey::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog & Inventory';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'License Keys';

    protected static ?string $recordTitleAttribute = 'key';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', LicenseKeyStatus::Available)->count();

        return (string) $count;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = (int) static::getNavigationBadge();

        return $count > 0 ? 'success' : 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Available in-stock license keys';
    }

    public static function form(Schema $schema): Schema
    {
        return LicenseKeyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LicenseKeysTable::configure($table);
    }

    public static function getWidgets(): array
    {
        return [
            LicenseKeyStatsOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLicenseKeys::route('/'),
        ];
    }
}
