<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MonthlyRevenueChartWidget extends ChartWidget
{
    protected static ?string $heading   = 'Monthly Revenue — Last 6 Months (ETB)';
    protected static ?int    $sort      = 6;
    protected static ?string $maxHeight = '200px';

    public function getColumnSpan(): int | string | array
    {
        return 'full';
    }

    protected function getData(): array
    {
        $labels  = [];
        $revenue = [];
        $vat     = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);

            $sales = Sale::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->get();

            $labels[]  = $month->format('M Y');
            $revenue[] = round($sales->sum('total'), 2);
            $vat[]     = round($sales->sum('vat_amount'), 2);
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Total Revenue (ETB)',
                    'data'            => $revenue,
                    'backgroundColor' => 'rgba(245,158,11,0.75)',
                    'borderColor'     => 'rgba(245,158,11,1)',
                    'borderWidth'     => 1,
                    'borderRadius'    => 4,
                ],
                [
                    'label'           => 'VAT Collected (ETB)',
                    'data'            => $vat,
                    'backgroundColor' => 'rgba(59,130,246,0.65)',
                    'borderColor'     => 'rgba(59,130,246,1)',
                    'borderWidth'     => 1,
                    'borderRadius'    => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['stacked' => false],
                'y' => ['beginAtZero' => true, 'title' => ['display' => true, 'text' => 'ETB']],
            ],
            'plugins' => [
                'legend'  => ['position' => 'top'],
                'tooltip' => ['mode' => 'index', 'intersect' => false],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
