<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth(Width::TwoExtraLarge)
                ->modalHeading('Create Category')
                ->modalDescription('Add a new product category or subcategory with SEO metadata.')
                ->modalIcon('heroicon-o-folder-plus'),
        ];
    }
}
