<?php

namespace App\Filament\Resources\PromoCodes;

use App\Filament\Resources\PromoCodes\Pages\CreatePromoCode;
use App\Filament\Resources\PromoCodes\Pages\EditPromoCode;
use App\Filament\Resources\PromoCodes\Pages\ListPromoCodes;
use App\Filament\Resources\PromoCodes\Pages\ViewPromoCode;
use App\Filament\Resources\PromoCodes\RelationManagers\PromoCodeUsagesRelationManager;
use App\Filament\Resources\PromoCodes\Schemas\PromoCodeForm;
use App\Filament\Resources\PromoCodes\Schemas\PromoCodeInfolist;
use App\Filament\Resources\PromoCodes\Tables\PromoCodesTable;
use App\Filament\Resources\PromoCodes\Widgets\PromoCodeStatsOverview;
use App\Models\PromoCode;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PromoCodeResource extends Resource
{
    protected static ?string $model = PromoCode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing & Storefront';

    protected static ?string $navigationLabel = 'Promo Codes';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'code';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('is_active', true)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return PromoCodeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PromoCodeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PromoCodesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PromoCodeUsagesRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            PromoCodeStatsOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPromoCodes::route('/'),
            'create' => CreatePromoCode::route('/create'),
            'view' => ViewPromoCode::route('/{record}'),
            'edit' => EditPromoCode::route('/{record}/edit'),
        ];
    }
}
