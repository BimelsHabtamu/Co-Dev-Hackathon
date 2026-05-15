<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockReportExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private Collection $products,
        private string $preparedBy
    ) {}

    public function collection(): Collection
    {
        return $this->products->map(fn ($p) => [
            'Name'           => $p->name,
            'Category'       => $p->category,
            'SKU'            => $p->sku,
            'Cost Price'     => number_format($p->cost_price, 2),
            'Selling Price'  => number_format($p->selling_price, 2),
            'Stock Quantity' => $p->stock_quantity,
            'Status'         => $p->stock_quantity === 0 ? 'Out of Stock' : ($p->stock_quantity < 5 ? 'Low Stock' : 'In Stock'),
        ]);
    }

    public function headings(): array
    {
        return ['Name', 'Category', 'SKU', 'Cost Price (Birr)', 'Selling Price (Birr)', 'Stock Quantity', 'Status'];
    }

    public function title(): string
    {
        return 'Stock Report';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}
