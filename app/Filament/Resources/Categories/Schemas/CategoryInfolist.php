<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Category Details')
                    ->tabs([
                        Tab::make('Overview')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('name')
                                            ->label('Category Name')
                                            ->size(TextSize::Large)
                                            ->weight(FontWeight::Bold)
                                            ->icon('heroicon-m-tag'),

                                        TextEntry::make('status')
                                            ->label('Publishing Status')
                                            ->badge()
                                            ->formatStateUsing(fn (bool $state): string => $state ? 'Active & Published' : 'Hidden / Draft')
                                            ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                                            ->icon(fn (bool $state): string => $state ? 'heroicon-m-check-badge' : 'heroicon-m-eye-slash'),

                                        TextEntry::make('slug')
                                            ->label('Storefront URL')
                                            ->state(fn (Category $record): string => url('/category/'.$record->slug))
                                            ->icon('heroicon-m-arrow-top-right-on-square')
                                            ->color('primary')
                                            ->copyable()
                                            ->copyMessage('Storefront URL copied to clipboard'),

                                        TextEntry::make('parent.name')
                                            ->label('Parent Category')
                                            ->badge()
                                            ->color('gray')
                                            ->icon('heroicon-m-folder')
                                            ->placeholder('None (Root Category)'),

                                        TextEntry::make('sort_order')
                                            ->label('Display Sequence')
                                            ->badge()
                                            ->color('warning')
                                            ->icon('heroicon-m-arrows-up-down'),

                                        TextEntry::make('children.name')
                                            ->label('Subcategories')
                                            ->badge()
                                            ->color('info')
                                            ->icon('heroicon-m-tag')
                                            ->placeholder('None'),

                                        TextEntry::make('description')
                                            ->label('Description')
                                            ->placeholder('No description provided for this collection.')
                                            ->columnSpanFull(),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('created_at')
                                            ->label('Created On')
                                            ->dateTime('M d, Y · h:i A')
                                            ->helperText(fn (Category $record): ?string => $record->created_at?->diffForHumans()),

                                        TextEntry::make('updated_at')
                                            ->label('Last Updated')
                                            ->dateTime('M d, Y · h:i A')
                                            ->helperText(fn (Category $record): ?string => $record->updated_at?->diffForHumans()),
                                    ]),
                            ]),

                        Tab::make('Media & Branding')
                            ->icon('heroicon-m-photo')
                            ->schema([
                                ImageEntry::make('image')
                                    ->label('Cover / Banner Image')
                                    ->disk('public')
                                    ->defaultImageUrl(fn (): string => 'https://ui-avatars.com/api/?name=Category&background=f1f5f9&color=64748b')
                                    ->extraImgAttributes([
                                        'class' => 'rounded-xl shadow-sm border border-gray-100 dark:border-white/10 object-cover max-h-48 w-full',
                                    ]),

                                TextEntry::make('icon')
                                    ->label('Icon Identifier')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-m-sparkles')
                                    ->placeholder('Default Icon'),
                            ]),

                        Tab::make('Search Engine (SEO)')
                            ->icon('heroicon-m-globe-alt')
                            ->schema([
                                TextEntry::make('seo_preview')
                                    ->label('Google Search Result Preview')
                                    ->state(function (Category $record): string {
                                        $title = e($record->meta_title ?: $record->name.' - Buy Online');
                                        $url = e(url('/category/'.$record->slug));
                                        $desc = e($record->meta_description ?: ($record->description ?: 'Explore premium digital products and services in '.$record->name.'. Instant delivery and 24/7 verified access.'));

                                        return '<div class="p-3.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/80 dark:bg-white/5 space-y-1 font-sans">
                                            <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1.5 truncate">
                                                <span class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-[9px] font-bold">✓</span>
                                                <span class="truncate">'.$url.'</span>
                                            </div>
                                            <div class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                                '.$title.'
                                            </div>
                                            <div class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                                                '.$desc.'
                                            </div>
                                        </div>';
                                    })
                                    ->html()
                                    ->columnSpanFull(),

                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('meta_title')
                                            ->label('Meta Title')
                                            ->placeholder('Auto-generated from name')
                                            ->icon('heroicon-m-document-text'),

                                        TextEntry::make('meta_description')
                                            ->label('Meta Description')
                                            ->placeholder('Auto-generated from description')
                                            ->icon('heroicon-m-chat-bubble-bottom-center-text'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
