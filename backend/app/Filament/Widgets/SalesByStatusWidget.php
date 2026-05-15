<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;

class SalesByStatusWidget extends ChartWidget
{
    protected static ?string $heading   = 'Sales by Status (This Month)';
    protected static ?int    $sort      = 4;
    protected static ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $statuses = ['completed', 'pending_approval', 'approved', 'rejected'];

        $counts = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $data   = array_map(fn ($s) => $counts[$s] ?? 0, $statuses);
        $labels = ['Completed', 'Pending Approval', 'Approved', 'Rejected'];

        return [
            'datasets' => [
                [
                    'data'            => $data,
                    'backgroundColor' => [
                        'rgba(34,197,94,0.85)',
                        'rgba(234,179,8,0.85)',
                        'rgba(59,130,246,0.85)',
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
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
