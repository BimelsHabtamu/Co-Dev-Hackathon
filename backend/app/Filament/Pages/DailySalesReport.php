<?php

namespace App\Filament\Pages;

use App\Exports\SalesReportExport;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class DailySalesReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Daily Report';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int    $navigationSort  = 10;
    protected static string  $view            = 'filament.pages.daily-sales-report';

    public ?string $date = '';
    public Collection $sales;
    public float $totalRevenue   = 0;
    public float $totalVat       = 0;
    public float $totalDiscount  = 0;
    public bool  $generated      = false;

    public function mount(): void
    {
        $this->date  = now()->toDateString();
        $this->sales = collect();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            DatePicker::make('date')
                ->label('Select Date')
                ->required()
                ->default(now()->toDateString()),
        ]);
    }

    public function generate(): void
    {
        $this->sales = Sale::with(['user:id,name', 'items.product:id,name,sku'])
            ->whereDate('created_at', $this->date)
            ->latest()
            ->get();

        $this->totalRevenue  = round($this->sales->sum('total'), 2);
        $this->totalVat      = round($this->sales->sum('vat_amount'), 2);
        $this->totalDiscount = round($this->sales->sum('discount_amount'), 2);
        $this->generated     = true;

        Notification::make()->title('Report generated for ' . $this->date)->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->visible(fn () => $this->generated && $this->sales->isNotEmpty())
                ->action(function () {
                    $title = 'Daily Sales Report — ' . $this->date;
                    return Excel::download(
                        new SalesReportExport($this->sales, $title, auth()->user()->name),
                        'daily-sales-' . $this->date . '.xlsx'
                    );
                }),

            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->visible(fn () => $this->generated && $this->sales->isNotEmpty())
                ->action(function () {
                    $data = [
                        'title'          => 'Daily Sales Report — ' . $this->date,
                        'sales'          => $this->sales,
                        'total_sales'    => $this->sales->count(),
                        'total_revenue'  => $this->totalRevenue,
                        'total_vat'      => $this->totalVat,
                        'total_discount' => $this->totalDiscount,
                        'prepared_by'    => auth()->user()->name,
                        'generated_at'   => now()->toDateTimeString(),
                    ];
                    $pdf = Pdf::loadView('reports.sales', $data)->setPaper('a4', 'landscape');
                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'daily-sales-' . $this->date . '.pdf'
                    );
                }),
        ];
    }
}
