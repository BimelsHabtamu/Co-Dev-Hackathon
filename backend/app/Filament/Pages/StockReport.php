<?php

namespace App\Filament\Pages;

use App\Exports\StockReportExport;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class StockReport extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Stock Report';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int    $navigationSort  = 12;
    protected static string  $view            = 'filament.pages.stock-report';

    public Collection $products;
    public int   $totalProducts = 0;
    public int   $outOfStock    = 0;
    public int   $lowStock      = 0;
    public int   $inStock       = 0;
    public bool  $generated     = false;

    public function mount(): void
    {
        $this->products = collect();
        $this->generate();
    }

    public function generate(): void
    {
        $this->products      = Product::orderBy('name')->get();
        $this->totalProducts = $this->products->count();
        $this->outOfStock    = $this->products->where('stock_quantity', 0)->count();
        $this->lowStock      = $this->products->where('stock_quantity', '>', 0)->where('stock_quantity', '<', 5)->count();
        $this->inStock       = $this->totalProducts - $this->outOfStock - $this->lowStock;
        $this->generated     = true;

        Notification::make()->title('Stock report refreshed.')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => $this->generate()),

            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action(function () {
                    return Excel::download(
                        new StockReportExport($this->products, auth()->user()->name),
                        'stock-report-' . now()->toDateString() . '.xlsx'
                    );
                }),

            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    $data = [
                        'products'       => $this->products,
                        'total_products' => $this->totalProducts,
                        'out_of_stock'   => $this->outOfStock,
                        'low_stock'      => $this->lowStock,
                        'prepared_by'    => auth()->user()->name,
                        'generated_at'   => now()->toDateTimeString(),
                    ];
                    $pdf = Pdf::loadView('reports.stock', $data)->setPaper('a4', 'portrait');
                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'stock-report-' . now()->toDateString() . '.pdf'
                    );
                }),
        ];
    }
}
