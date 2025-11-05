<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\InboundVsOutboundChart;
use App\Filament\Widgets\LowStockTable;
use App\Filament\Widgets\RecentActivityTable;
use App\Filament\Widgets\RecentOutboundTable;
use App\Filament\Widgets\SalesChart;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\TopProductsTable;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
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
            ->brandName('Bosco Warehouse System')
            ->brandLogo(function () {
                // Check if Filament is in dark mode
                $isDarkMode = (bool) (request()->cookie('filament_dark_mode') ?? false);
                
                if ($isDarkMode) {
                    return asset('images/logo_dark.png');
                } elseif (request()->cookie('filament_dark_mode') !== null) {
                    return asset('images/logo_light.png');
                }
                
                return asset('images/logo.png');
            })
            ->brandLogoHeight('4rem')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Blue,
                'danger' => Color::Red,
                'gray' => Color::Zinc,
                'info' => Color::Sky,
                'success' => Color::Green,
                'warning' => Color::Amber,
            ])
            ->navigationGroups([
                NavigationGroup::make('Master Data')
                    ->icon('heroicon-o-folder')
                    ->collapsed(false),
                NavigationGroup::make('Transactions')
                    ->icon('heroicon-o-arrow-path')
                    ->collapsed(false),
                NavigationGroup::make('Stock Management')
                    ->icon('heroicon-o-cube-transparent')
                    ->collapsed(false),
                NavigationGroup::make('Documents')
                    ->icon('heroicon-o-document-duplicate')
                    ->collapsed(false),
                NavigationGroup::make('Reports')
                    ->icon('heroicon-o-chart-bar')
                    ->collapsed(false),
                NavigationGroup::make('Settings')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(false),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->plugin(FilamentShieldPlugin::make())
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
                StatsOverview::class,
                SalesChart::class,
                InboundVsOutboundChart::class,
                TopProductsTable::class,
                LowStockTable::class,
                RecentActivityTable::class,
                RecentOutboundTable::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s');
    }
}
