<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\LicenseKeyStatus;
use App\Models\LicenseKey;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Product Configuration')
                    ->tabs([
                        // Tab 1: Basic Information
                        Tab::make('General Info')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Grid::make(12)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Product Name')
                                            ->placeholder('e.g. FF Headshot Master VIP Injector')
                                            ->prefixIcon('heroicon-m-tag')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (?string $state, callable $set, string $operation): void {
                                                if ($operation === 'create' && filled($state)) {
                                                    $set('slug', Str::slug($state));
                                                }
                                            })
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 12,
                                            ]),

                                        Select::make('category_id')
                                            ->label('Product Category')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->prefixIcon('heroicon-m-folder')
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 6,
                                            ]),

                                        Select::make('type')
                                            ->label('Product Type')
                                            ->options([
                                                'digital_key' => 'Digital License Key',
                                                'service' => 'Manual Service (e.g. Rooting)',
                                            ])
                                            ->default('digital_key')
                                            ->required()
                                            ->prefixIcon('heroicon-m-squares-plus')
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 6,
                                            ]),
                                    ]),

                                Grid::make(12)
                                    ->schema([
                                        TextInput::make('slug')
                                            ->label('URL Slug')
                                            ->prefix('/product/')
                                            ->prefixIcon('heroicon-m-link')
                                            ->placeholder('ff-headshot-master-vip')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 7,
                                            ]),

                                        TextInput::make('sort_order')
                                            ->label('Display Sequence')
                                            ->numeric()
                                            ->default(0)
                                            ->prefixIcon('heroicon-m-arrows-up-down')
                                            ->helperText('Lower sequence numbers appear first (0 = default).')
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 5,
                                            ]),
                                    ]),

                                Textarea::make('description')
                                    ->label('Description')
                                    ->placeholder('Detailed features, installation guide, and compatibility...')
                                    ->rows(3),

                                TagsInput::make('features')
                                    ->label('Key Features & Selling Points')
                                    ->placeholder('Type feature and press Enter (e.g. Anti-Ban, Instant Key)')
                                    ->prefixIcon('heroicon-m-check-badge')
                                    ->helperText('Bullet points highlighted on product checkout card.'),

                                Grid::make(12)
                                    ->schema([
                                        TextInput::make('demo_video_url')
                                            ->label('Demo Video URL')
                                            ->placeholder('https://www.youtube.com/watch?v=...')
                                            ->prefixIcon('heroicon-m-play-circle')
                                            ->url()
                                            ->maxLength(500)
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 7,
                                            ]),

                                        Toggle::make('status')
                                            ->label('Storefront Status')
                                            ->inline(false)
                                            ->default(true)
                                            ->onColor('success')
                                            ->offColor('danger')
                                            ->onIcon('heroicon-m-check')
                                            ->offIcon('heroicon-m-x-mark')
                                            ->helperText('Visible to customers when enabled.')
                                            ->columnSpan([
                                                'default' => 6,
                                                'md' => 2,
                                            ]),

                                        Toggle::make('is_maintenance')
                                            ->label('Maintenance Mode')
                                            ->inline(false)
                                            ->default(false)
                                            ->onColor('warning')
                                            ->offColor('gray')
                                            ->onIcon('heroicon-m-wrench-screwdriver')
                                            ->offIcon('heroicon-m-minus')
                                            ->helperText('Disable purchases temporarily.')
                                            ->columnSpan([
                                                'default' => 6,
                                                'md' => 3,
                                            ]),
                                    ]),

                                Grid::make(12)
                                    ->schema([
                                        FileUpload::make('setup_file_path')
                                            ->label('Product Setup File (Optional)')
                                            ->directory('product-setups')
                                            ->disk('public')
                                            ->preserveFilenames()
                                            ->helperText('Upload an APK, ZIP, or EXE file for this product.')
                                            ->maxSize(51200)
                                            ->acceptedFileTypes(['application/vnd.android.package-archive', 'application/zip', 'application/x-zip-compressed', 'application/x-msdownload', 'application/x-ms-dos-executable'])
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 6,
                                            ]),

                                        TextInput::make('setup_link')
                                            ->label('Or Setup Link (Optional)')
                                            ->placeholder('https://drive.google.com/... or https://mega.nz/...')
                                            ->url()
                                            ->prefixIcon('heroicon-m-link')
                                            ->helperText('Provide an external link instead of uploading a file.')
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 6,
                                            ]),
                                    ]),
                            ]),

                        // Tab 2: Duration Variants & Pricing
                        Tab::make('Variants & Pricing')
                            ->icon('heroicon-m-currency-bangladeshi')
                            ->schema([
                                Repeater::make('variants')
                                    ->relationship('variants')
                                    ->minItems(1)
                                    ->defaultItems(1)
                                    ->reorderableWithButtons()
                                    ->collapsible()
                                    ->itemLabel(function (array $state): ?string {
                                        $title = $state['duration_name'] ?? 'New Duration Tier';
                                        $price = isset($state['regular_price']) ? ' · ৳'.$state['regular_price'] : '';

                                        if (! empty($state['id'])) {
                                            $count = LicenseKey::where('product_variant_id', $state['id'])
                                                ->where('status', LicenseKeyStatus::Available)
                                                ->count();

                                            $stock = $count > 0 ? " · [{$count} in stock]" : ' · [Out of stock]';

                                            return $title.$price.$stock;
                                        }

                                        return $title.$price;
                                    })
                                    ->schema([
                                        Grid::make(12)
                                            ->schema([
                                                TextInput::make('duration_name')
                                                    ->label('Duration Name')
                                                    ->placeholder('e.g. 30 Days VIP')
                                                    ->prefixIcon('heroicon-m-clock')
                                                    ->required()
                                                    ->maxLength(100)
                                                    ->columnSpan([
                                                        'default' => 12,
                                                        'md' => 6,
                                                    ]),

                                                TextInput::make('duration_days')
                                                    ->label('Validity (Days)')
                                                    ->numeric()
                                                    ->default(30)
                                                    ->prefixIcon('heroicon-m-calendar')
                                                    ->required()
                                                    ->minValue(0)
                                                    ->columnSpan([
                                                        'default' => 6,
                                                        'md' => 3,
                                                    ]),

                                                TextInput::make('api_provider_id')
                                                    ->label('Supplier Package ID')
                                                    ->placeholder('e.g. 1042')
                                                    ->prefixIcon('heroicon-m-bolt')
                                                    ->columnSpan([
                                                        'default' => 6,
                                                        'md' => 3,
                                                    ]),
                                            ]),

                                        Grid::make(12)
                                            ->schema([
                                                TextInput::make('regular_price')
                                                    ->label('Regular Price')
                                                    ->prefix('৳')
                                                    ->placeholder('0.00')
                                                    ->numeric()
                                                    ->required()
                                                    ->minValue(0)
                                                    ->columnSpan([
                                                        'default' => 12,
                                                        'sm' => 6,
                                                        'md' => 3,
                                                    ]),

                                                TextInput::make('offer_price')
                                                    ->label('Offer Price')
                                                    ->prefix('৳')
                                                    ->placeholder('Optional')
                                                    ->numeric()
                                                    ->nullable()
                                                    ->minValue(0)
                                                    ->lte('regular_price')
                                                    ->columnSpan([
                                                        'default' => 12,
                                                        'sm' => 6,
                                                        'md' => 3,
                                                    ]),

                                                TextInput::make('cost_price')
                                                    ->label('Cost Price')
                                                    ->prefix('৳')
                                                    ->placeholder('Wholesale')
                                                    ->numeric()
                                                    ->nullable()
                                                    ->minValue(0)
                                                    ->columnSpan([
                                                        'default' => 12,
                                                        'sm' => 6,
                                                        'md' => 3,
                                                    ]),

                                                Toggle::make('is_popular')
                                                    ->label('Popular Badge')
                                                    ->inline(false)
                                                    ->onColor('warning')
                                                    ->onIcon('heroicon-m-star')
                                                    ->offIcon('heroicon-m-star')
                                                    ->helperText('Highlight as best seller')
                                                    ->columnSpan([
                                                        'default' => 12,
                                                        'sm' => 6,
                                                        'md' => 3,
                                                    ]),
                                            ]),
                                    ]),
                            ]),

                        // Tab 3: Media & Assets
                        Tab::make('Media & Assets')
                            ->icon('heroicon-m-photo')
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Product Cover Image')
                                    ->image()
                                    ->directory('products')
                                    ->disk('public')
                                    ->imageEditor()
                                    ->maxSize(2048)
                                    ->helperText('Recommended 16:9 banner or square card (max 2MB).'),

                                TextInput::make('icon')
                                    ->label('Icon Identifier')
                                    ->placeholder('e.g. heroicon-o-fire')
                                    ->prefixIcon('heroicon-m-sparkles')
                                    ->default('heroicon-o-cube')
                                    ->helperText('Heroicon name or CSS class for storefront listing.'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
