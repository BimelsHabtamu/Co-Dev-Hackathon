<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading    = 'Revenue — Last 7 Days (ETB)';
    protected static ?int    $sort       = 2;
    protected static ?string $maxHeight  = '220px';

    public function getColumnSpan(): int | string | array
    {
        return 'full';
    }

    protected function getData(): array
    {
        $labels   = [];
        $revenue  = [];
        $salesQty = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            $daySales = Sale::whereDate('created_at', $date)->get();

            $labels[]   = $date->format('D d/m');
            $revenue[]  = round($daySales->sum('total'), 2);
            $salesQty[] = $daySales->count();
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Revenue (ETB)',
                    'data'            => $revenue,
                    'backgroundColor' => 'rgba(245,158,11,0.25)',
                    'borderColor'     => 'rgba(245,158,11,1)',
                    'borderWidth'     => 2,
                    'fill'            => true,
                    'tension'         => 0.4,
                    'pointBackgroundColor' => 'rgba(245,158,11,1)',
                    'pointRadius'     => 4,
                ],
                [
                    'label'           => 'Transactions',
                    'data'            => $salesQty,
                    'backgroundColor' => 'rgba(59,130,246,0.15)',
                    'borderColor'     => 'rgba(59,130,246,0.8)',
                    'borderWidth'     => 2,
                    'fill'            => false,
                    'tension'         => 0.4,
                    'pointBackgroundColor' => 'rgba(59,130,246,1)',
                    'pointRadius'     => 4,
                    'yAxisID'         => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y'  => ['beginAtZero' => true, 'title' => ['display' => true, 'text' => 'ETB']],
                'y1' => [
                    'beginAtZero' => true,
                    'position'    => 'right',
                    'grid'        => ['drawOnChartArea' => false],
                    'title'       => ['display' => true, 'text' => 'Transactions'],
                ],
            ],
            'plugins' => [
                'legend' => ['position' => 'top'],
                'tooltip' => ['mode' => 'index', 'intersect' => false],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
