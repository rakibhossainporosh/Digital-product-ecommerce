<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\SettingService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;

class ManageSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'settings';

    protected static ?string $title = 'App Settings';

    protected static ?string $navigationLabel = 'Settings';

    protected ?string $heading = 'Application Settings & Maintenance';

    protected ?string $subheading = 'Centrally manage system identity, maintenance mode, referral rewards, payment gateways, and supplier panel credentials.';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user && ($user->hasRole('super_admin') || $user->can('View:Setting') || $user->can('Update:Setting'));
    }

    public function mount(SettingService $settingService): void
    {
        $settings = $settingService->all();
        $this->form->fill($settings);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings Configuration')
                    ->tabs([
                        Tab::make('General & Identity')
                            ->icon('heroicon-m-globe-alt')
                            ->schema([
                                Section::make('Store Identity')
                                    ->description('Public branding, store name, and customer support contacts.')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('app_name')
                                                ->label('App / Store Name')
                                                ->placeholder('Digital Product Store')
                                                ->prefixIcon('heroicon-m-building-storefront')
                                                ->required()
                                                ->maxLength(100),

                                            TextInput::make('app_tagline')
                                                ->label('Store Tagline')
                                                ->placeholder('Instant Digital License & Account Delivery')
                                                ->prefixIcon('heroicon-m-sparkles')
                                                ->maxLength(255),
                                        ]),

                                        Grid::make(3)->schema([
                                            TextInput::make('currency_symbol')
                                                ->label('Currency Symbol')
                                                ->placeholder('৳')
                                                ->default('৳')
                                                ->required()
                                                ->maxLength(10),

                                            TextInput::make('support_email')
                                                ->label('Support Email')
                                                ->placeholder('support@example.com')
                                                ->prefixIcon('heroicon-m-envelope')
                                                ->email()
                                                ->maxLength(150),

                                            TextInput::make('support_whatsapp')
                                                ->label('Support WhatsApp')
                                                ->placeholder('+8801700000000')
                                                ->prefixIcon('heroicon-m-chat-bubble-left-ellipsis')
                                                ->maxLength(50),
                                        ]),
                                    ]),
                            ]),

                        Tab::make('Maintenance Mode')
                            ->icon('heroicon-m-wrench-screwdriver')
                            ->schema([
                                Section::make('System Maintenance & Emergency Controls')
                                    ->description('Temporarily close customer storefront for upgrades while keeping admin panel fully accessible.')
                                    ->schema([
                                        Toggle::make('maintenance_mode')
                                            ->label('Enable Maintenance Mode')
                                            ->helperText('When enabled, customer storefront renders maintenance page. Admin panel (/admin) remains completely accessible.')
                                            ->onColor('danger')
                                            ->offColor('success'),

                                        TextInput::make('maintenance_headline')
                                            ->label('Maintenance Headline')
                                            ->placeholder('Under Scheduled Maintenance')
                                            ->required()
                                            ->maxLength(150),

                                        Textarea::make('maintenance_message')
                                            ->label('Maintenance Notice Message')
                                            ->placeholder('Our platform is currently undergoing scheduled maintenance...')
                                            ->rows(3)
                                            ->required()
                                            ->columnSpanFull(),

                                        TextInput::make('maintenance_bypass_key')
                                            ->label('Secret Maintenance Bypass Key')
                                            ->placeholder('e.g. preview-maintenance-key-2026')
                                            ->helperText('Use ?bypass_key=<key> on any storefront URL to bypass maintenance mode.')
                                            ->prefixIcon('heroicon-m-key')
                                            ->maxLength(100),
                                    ]),
                            ]),

                        Tab::make('Financial & Referral')
                            ->icon('heroicon-m-banknotes')
                            ->schema([
                                Section::make('Wallet & Referral Policies')
                                    ->description('Configure customer wallet deposit boundaries and affiliate commission rates.')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            TextInput::make('referral_commission_percentage')
                                                ->label('Referral Commission (%)')
                                                ->numeric()
                                                ->suffix('%')
                                                ->helperText('Percentage awarded to referrer upon referred customer order completion.')
                                                ->minValue(0)
                                                ->maxValue(100),

                                            TextInput::make('min_deposit_amount')
                                                ->label('Minimum Wallet Deposit')
                                                ->numeric()
                                                ->prefix('৳')
                                                ->helperText('Minimum acceptable amount per wallet top-up.')
                                                ->minValue(1),

                                            TextInput::make('max_deposit_amount')
                                                ->label('Maximum Wallet Deposit')
                                                ->numeric()
                                                ->prefix('৳')
                                                ->helperText('Maximum ceiling per wallet deposit transaction.')
                                                ->minValue(1),
                                        ]),
                                    ]),
                            ]),

                        Tab::make('Payment Gateway')
                            ->icon('heroicon-m-credit-card')
                            ->schema([
                                Section::make('Automated Payment Gateway Credentials')
                                    ->description('API credentials for automated customer wallet checkout and instant payments.')
                                    ->schema([
                                        Toggle::make('gateway_enabled')
                                            ->label('Enable Automated Payment Gateway')
                                            ->helperText('Allow customers to initiate automated online payments.')
                                            ->onColor('success'),

                                        TextInput::make('gateway_base_url')
                                            ->label('Gateway API Endpoint / Base URL')
                                            ->placeholder('https://sandbox.uddoktapay.com/api/checkout-v2')
                                            ->prefixIcon('heroicon-m-link')
                                            ->url()
                                            ->maxLength(255),

                                        TextInput::make('gateway_api_key')
                                            ->label('Gateway API Secret Key')
                                            ->placeholder('Enter UddoktaPay API Secret Key')
                                            ->prefixIcon('heroicon-m-lock-closed')
                                            ->password()
                                            ->revealable()
                                            ->helperText('Encrypted with AES-256 at rest in the database.'),
                                    ]),
                            ]),

                        Tab::make('Supplier API')
                            ->icon('heroicon-m-cpu-chip')
                            ->schema([
                                Section::make('External Supplier Integration')
                                    ->description('External automated API fulfillment provider settings.')
                                    ->schema([
                                        Toggle::make('supplier_api_enabled')
                                            ->label('Enable External Supplier Panel')
                                            ->helperText('Dispatch order to external supplier panel when local inventory runs out.')
                                            ->onColor('primary'),

                                        TextInput::make('supplier_api_url')
                                            ->label('Supplier Panel API URL')
                                            ->placeholder('https://api.supplierpanel.example/v1')
                                            ->prefixIcon('heroicon-m-link')
                                            ->url()
                                            ->maxLength(255),

                                        TextInput::make('supplier_api_key')
                                            ->label('Supplier Panel API Key / Token')
                                            ->placeholder('Enter supplier secret API token')
                                            ->prefixIcon('heroicon-m-key')
                                            ->password()
                                            ->revealable()
                                            ->helperText('Encrypted with AES-256 at rest in the database.'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment(Alignment::Start)
                    ->key('form-actions'),
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->icon('heroicon-m-check')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('flushCache')
                ->label('Flush Cache')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function (SettingService $settingService): void {
                    $settingService->clearCache();

                    Notification::make()
                        ->title('Settings Cache Flushed')
                        ->body('Cached settings have been cleared and refreshed from database.')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function save(SettingService $settingService): void
    {
        $state = $this->form->getState();

        $definitions = [
            'app_name' => ['group' => 'general', 'type' => 'string', 'is_public' => true],
            'app_tagline' => ['group' => 'general', 'type' => 'string', 'is_public' => true],
            'currency_symbol' => ['group' => 'general', 'type' => 'string', 'is_public' => true],
            'support_email' => ['group' => 'general', 'type' => 'string', 'is_public' => true],
            'support_whatsapp' => ['group' => 'general', 'type' => 'string', 'is_public' => true],

            'maintenance_mode' => ['group' => 'maintenance', 'type' => 'boolean', 'is_public' => true],
            'maintenance_headline' => ['group' => 'maintenance', 'type' => 'string', 'is_public' => true],
            'maintenance_message' => ['group' => 'maintenance', 'type' => 'string', 'is_public' => true],
            'maintenance_bypass_key' => ['group' => 'maintenance', 'type' => 'string', 'is_public' => false],

            'referral_commission_percentage' => ['group' => 'financial', 'type' => 'float', 'is_public' => false],
            'min_deposit_amount' => ['group' => 'financial', 'type' => 'float', 'is_public' => true],
            'max_deposit_amount' => ['group' => 'financial', 'type' => 'float', 'is_public' => true],

            'gateway_enabled' => ['group' => 'gateway', 'type' => 'boolean', 'is_public' => false],
            'gateway_base_url' => ['group' => 'gateway', 'type' => 'string', 'is_public' => false],
            'gateway_api_key' => ['group' => 'gateway', 'type' => 'encrypted', 'is_public' => false],

            'supplier_api_enabled' => ['group' => 'supplier', 'type' => 'boolean', 'is_public' => false],
            'supplier_api_url' => ['group' => 'supplier', 'type' => 'string', 'is_public' => false],
            'supplier_api_key' => ['group' => 'supplier', 'type' => 'encrypted', 'is_public' => false],
        ];

        foreach ($state as $key => $val) {
            if (! isset($definitions[$key])) {
                continue;
            }

            $def = $definitions[$key];
            $settingService->set(
                key: $key,
                value: $val,
                group: $def['group'],
                type: $def['type'],
                isPublic: $def['is_public'],
            );
        }

        Notification::make()
            ->title('Settings Saved')
            ->body('Application configurations have been updated successfully.')
            ->success()
            ->send();
    }
}
