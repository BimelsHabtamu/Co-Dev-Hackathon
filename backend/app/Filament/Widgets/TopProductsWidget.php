<?php

namespace App\Filament\Widgets;

use App\Models\SaleItem;
use Filament\Widgets\ChartWidget;

class TopProductsWidget extends ChartWidget
{
    protected static ?string $heading   = 'Top 5 Selling Products (All Time)';
    protected static ?int    $sort      = 3;
    protected static ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $top = SaleItem::selectRaw('product_id, SUM(quantity) as total_qty')
            ->with('product:id,name')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $labels = $top->map(fn ($i) => $i->product?->name ?? "Product #{$i->product_id}")->toArray();
        $data   = $top->pluck('total_qty')->toArray();

        return [
            'datasets' => [
                [
                    'label'           => 'Units Sold',
                    'data'            => $data,
                    'backgroundColor' => [
                        'rgba(245,158,11,0.85)',
                        'rgba(59,130,246,0.85)',
                        'rgba(168,85,247,0.85)',
                        'rgba(34,197,94,0.85)',
                        'rgba(239,68,68,0.85)',
                    ],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'right'],
            ],
            'cutout' => '60%',
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
