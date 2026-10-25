<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->spa()
            ->sidebarCollapsibleOnDesktop()
            ->brandName(fn (): string => (string) setting('app_name', 'Panel Sell'))
            ->brandLogo(function () {
                $name = (string) setting('app_name', 'Panel Sell');
                $logo = setting('site_logo');

                if ($logo) {
                    $url = asset('storage/'.$logo);

                    return new HtmlString('
                        <div style="display: inline-flex; align-items: center; gap: 0.625rem; height: 100%; max-height: 2.5rem;">
                            <img src="'.$url.'" alt="'.e($name).'" style="height: 2.25rem; max-height: 2.25rem; width: auto; max-width: 140px; object-fit: contain; border-radius: 0.375rem; flex-shrink: 0; display: block;" />
                            <div style="display: flex; flex-direction: column; text-align: left; min-width: 0;">
                                <span class="text-gray-950 dark:text-white" style="font-weight: 700; font-size: 0.95rem; line-height: 1.2; letter-spacing: -0.025em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">'.e($name).'</span>
                                <span style="font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; line-height: 1; color: #9ca3af;">Store Admin</span>
                            </div>
                        </div>
                    ');
                }

                $firstLetter = mb_substr($name, 0, 1) ?: 'P';

                return new HtmlString('
                    <div style="display: inline-flex; align-items: center; gap: 0.625rem; height: 100%; max-height: 2.5rem;">
                        <div style="height: 2rem; width: 2rem; min-width: 2rem; border-radius: 0.5rem; background: linear-gradient(135deg, #f82803, #730505); display: flex; align-items: center; justify-content: center; color: #ffffff; font-weight: 700; font-size: 0.875rem; box-shadow: 0 4px 6px -1px rgba(248, 40, 3, 0.2); flex-shrink: 0;">
                            '.$firstLetter.'
                        </div>
                        <div style="display: flex; flex-direction: column; text-align: left; min-width: 0;">
                            <span class="text-gray-950 dark:text-white" style="font-weight: 700; font-size: 0.95rem; line-height: 1.2; letter-spacing: -0.025em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">'.e($name).'</span>
                            <span style="font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; line-height: 1; color: #9ca3af;">Store Admin</span>
                        </div>
                    </div>
                ');
            })
            ->brandLogoHeight('2.5rem')
            ->favicon(fn (): ?string => setting('site_favicon') ? asset('storage/'.setting('site_favicon')) : null)
            ->colors([
                'primary' => Color::hex('#f82803'),
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('View Storefront')
                    ->url('/')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->openUrlInNewTab(),
            ])
            ->navigationGroups([
                NavigationGroup::make('Sales & Orders')
                    ->icon('heroicon-o-shopping-bag'),
                NavigationGroup::make('Catalog & Inventory')
                    ->icon('heroicon-o-rectangle-stack'),
                NavigationGroup::make('Finance & Wallet')
                    ->icon('heroicon-o-banknotes'),
                NavigationGroup::make('Customer Management')
                    ->icon('heroicon-o-user-group'),
                NavigationGroup::make('Marketing & Storefront')
                    ->icon('heroicon-o-sparkles'),
                NavigationGroup::make('Access & Security')
                    ->icon('heroicon-o-shield-check'),
                NavigationGroup::make('System & Settings')
                    ->icon('heroicon-o-cog-6-tooth'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
