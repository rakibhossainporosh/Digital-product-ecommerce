<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Category Configuration')
                    ->tabs([
                        Tab::make('General Info')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Category Name')
                                            ->placeholder('e.g. Gaming Panels & Injectors')
                                            ->prefixIcon('heroicon-m-tag')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (?string $state, callable $set, string $operation): void {
                                                if ($operation === 'create' && filled($state)) {
                                                    $set('slug', Str::slug($state));
                                                }
                                            }),

                                        TextInput::make('slug')
                                            ->label('URL Slug')
                                            ->prefix('/category/')
                                            ->prefixIcon('heroicon-m-link')
                                            ->placeholder('gaming-panels-injectors')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true),
                                    ]),

                                Textarea::make('description')
                                    ->label('Description')
                                    ->placeholder('Provide a brief overview for customers browsing this category...')
                                    ->rows(2),

                                Grid::make(2)
                                    ->schema([
                                        Select::make('parent_id')
                                            ->label('Parent Category')
                                            ->relationship(
                                                name: 'parent',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: fn (Builder $query, ?Category $record) => $record
                                                    ? $query->where('id', '!=', $record->id)->whereNull('parent_id')
                                                    : $query->whereNull('parent_id')
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('None (Root Category)')
                                            ->prefixIcon('heroicon-m-folder'),

                                        TextInput::make('sort_order')
                                            ->label('Display Sequence')
                                            ->numeric()
                                            ->default(0)
                                            ->prefixIcon('heroicon-m-arrows-up-down')
                                            ->helperText('Lower sequence numbers appear first (0 = default).'),
                                    ]),

                                Toggle::make('status')
                                    ->label('Storefront Active Status')
                                    ->default(true)
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->onIcon('heroicon-m-check')
                                    ->offIcon('heroicon-m-x-mark')
                                    ->helperText('When enabled, this category is visible to customers on your storefront.'),
                            ]),

                        Tab::make('Media & Assets')
                            ->icon('heroicon-m-photo')
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Cover / Banner Image')
                                    ->image()
                                    ->directory('categories')
                                    ->disk('public')
                                    ->imageEditor()
                                    ->maxSize(2048)
                                    ->helperText('Recommended aspect ratio 16:9 or square (max 2MB).'),

                                TextInput::make('icon')
                                    ->label('Icon Identifier')
                                    ->placeholder('e.g. heroicon-o-puzzle-piece')
                                    ->prefixIcon('heroicon-m-sparkles')
                                    ->helperText('Heroicon name or CSS class for storefront menus.'),
                            ]),

                        Tab::make('Search Engine (SEO)')
                            ->icon('heroicon-m-globe-alt')
                            ->schema([
                                TextInput::make('meta_title')
                                    ->label('Meta Title')
                                    ->placeholder('e.g. Buy Premium Gaming Panels & Injectors Online')
                                    ->prefixIcon('heroicon-m-document-text')
                                    ->maxLength(70)
                                    ->helperText('Recommended: 50-60 characters for best Google display.'),

                                Textarea::make('meta_description')
                                    ->label('Meta Description')
                                    ->placeholder('A concise summary of the category for search snippets...')
                                    ->rows(3)
                                    ->maxLength(160)
                                    ->helperText('Recommended: 150-160 characters.'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
