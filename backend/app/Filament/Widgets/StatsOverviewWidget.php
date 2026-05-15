<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public function getColumnSpan(): int | string | array
    {
        return 'full';
    }

    protected function getStats(): array
    {
        $todaySales   = Sale::whereDate('created_at', today())->get();
        $monthSales   = Sale::whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year)->get();
        $pendingCount = Sale::where('status', 'pending_approval')->count();
        $lowStock     = Product::where('stock_quantity', '>', 0)->where('stock_quantity', '<', 5)->count();
        $outOfStock   = Product::where('stock_quantity', 0)->count();
        $totalVat     = Sale::whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year)
                            ->sum('vat_amount');
        $officers     = User::role('sales_officer')->count();

        // 7-day revenue trend for sparkline description
        $yesterdaySales = Sale::whereDate('created_at', today()->subDay())->sum('total');
        $todayRevenue   = $todaySales->sum('total');
        $trend          = $yesterdaySales > 0
            ? round((($todayRevenue - $yesterdaySales) / $yesterdaySales) * 100, 1)
            : 0;

        return [
            Stat::make("Today's Revenue", 'ETB ' . number_format($todayRevenue, 2))
                ->description($todaySales->count() . ' transactions · ' . ($trend >= 0 ? '+' : '') . $trend . '% vs yesterday')
                ->descriptionIcon($trend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($trend >= 0 ? 'success' : 'danger'),

            Stat::make('Monthly Revenue', 'ETB ' . number_format($monthSales->sum('total'), 2))
                ->description($monthSales->count() . ' transactions · VAT: ETB ' . number_format($totalVat, 2))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Pending Approval', $pendingCount)
                ->description('Sales awaiting manager review')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingCount > 0 ? 'warning' : 'success'),

            Stat::make('Low / Out of Stock', $lowStock . ' / ' . $outOfStock)
                ->description('Products needing restocking')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($outOfStock > 0 ? 'danger' : ($lowStock > 0 ? 'warning' : 'success')),

            Stat::make('Sales Officers', $officers)
                ->description('Active sales accounts')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Total Products', \App\Models\Product::count())
                ->description('In catalog')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('gray'),
        ];
    }
}
