<?php

namespace App\Providers\Filament;

use App\Filament\Pages\DailySalesReport;
use App\Filament\Pages\MonthlySalesReport;
use App\Filament\Pages\StockReport;
use App\Filament\Widgets\MonthlyRevenueChartWidget;
use App\Filament\Widgets\RecentSalesWidget;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\SalesByStatusWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\TopProductsWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
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
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\Filament\Resources'
            )
            ->pages([
                Dashboard::class,
                DailySalesReport::class,
                MonthlySalesReport::class,
                StockReport::class,
            ])
            ->widgets([
                AccountWidget::class,

                // Row 1 — KPI stats (full width)
                StatsOverviewWidget::class,

                // Row 2 — 7-day revenue line chart (full width)
                RevenueChartWidget::class,

                // Row 3 — doughnut + pie side by side
                TopProductsWidget::class,
                SalesByStatusWidget::class,

                // Row 4 — 6-month bar chart (full width)
                MonthlyRevenueChartWidget::class,

                // Row 5 — recent sales table (full width)
                RecentSalesWidget::class,
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
            ]);
    }
}
