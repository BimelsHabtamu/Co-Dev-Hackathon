<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private Collection $sales,
        private string $title,
        private string $preparedBy
    ) {}

    public function collection(): Collection
    {
        return $this->sales->map(fn ($sale) => [
            'ID'           => '#' . $sale->id,
            'Cashier'      => $sale->user?->name ?? '—',
            'Items'        => $sale->items->count(),
            'Subtotal'     => number_format($sale->subtotal, 2),
            'Discount (%)'  => $sale->discount . '%',
            'Discount Amt' => number_format($sale->discount_amount, 2),
            'VAT (15%)'    => number_format($sale->vat_amount, 2),
            'Total (Birr)' => number_format($sale->total, 2),
            'Status'       => $sale->status,
            'Date'         => $sale->created_at->format('Y-m-d H:i'),
        ]);
    }

    public function headings(): array
    {
        return ['ID', 'Cashier', 'Items', 'Subtotal', 'Discount (%)', 'Discount Amt', 'VAT (15%)', 'Total (Birr)', 'Status', 'Date'];
    }

    public function title(): string
    {
        return 'Sales Report';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}
