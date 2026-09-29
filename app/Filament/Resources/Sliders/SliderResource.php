<?php

namespace App\Filament\Resources\Sliders;

use App\Filament\Resources\Sliders\Pages\ManageSliders;
use App\Models\Slider;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Storefront';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Slider Details')
                    ->schema([
                        TextInput::make('title')
                            ->label('Banner Title / Alt Text')
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('link_url')
                            ->label('Link URL')
                            ->url()
                            ->placeholder('https://example.com/promo')
                            ->columnSpan(1),

                        FileUpload::make('image_path')
                            ->label('Banner Image')
                            ->image()
                            ->imageEditor()
                            ->imageCropAspectRatio('21:9')
                            ->helperText('Recommended aspect ratio: 21:9. You can crop the image directly after uploading.')
                            ->disk('public')
                            ->directory('sliders')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2)->columnSpan(['lg' => 2]),

                Section::make('Settings')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->required(),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->required()
                            ->numeric()
                            ->default(0),
                    ])->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Banner')
                    ->size(80)
                    ->square(),
                TextColumn::make('title')
                    ->label('Title / Alt')
                    ->searchable()
                    ->placeholder('N/A'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSliders::route('/'),
        ];
    }
}
