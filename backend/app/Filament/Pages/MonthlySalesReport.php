<?php

namespace App\Filament\Pages;

use App\Exports\SalesReportExport;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class MonthlySalesReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Monthly Report';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int    $navigationSort  = 11;
    protected static string  $view            = 'filament.pages.monthly-sales-report';

    public ?string $month = '';
    public ?string $year  = '';
    public Collection $sales;
    public float $totalRevenue   = 0;
    public float $totalVat       = 0;
    public float $totalDiscount  = 0;
    public bool  $generated      = false;

    public function mount(): void
    {
        $this->month = now()->format('m');
        $this->year  = now()->format('Y');
        $this->sales = collect();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('month')
                ->label('Month')
                ->options([
                    '01' => 'January',   '02' => 'February', '03' => 'March',
                    '04' => 'April',     '05' => 'May',      '06' => 'June',
                    '07' => 'July',      '08' => 'August',   '09' => 'September',
                    '10' => 'October',   '11' => 'November', '12' => 'December',
                ])
                ->required()
                ->default(now()->format('m')),

            Select::make('year')
                ->label('Year')
                ->options(collect(range(now()->year, now()->year - 3))->mapWithKeys(fn ($y) => [$y => $y]))
                ->required()
                ->default(now()->format('Y')),
        ]);
    }

    public function generate(): void
    {
        $this->sales = Sale::with(['user:id,name', 'items.product:id,name,sku'])
            ->whereMonth('created_at', $this->month)
            ->whereYear('created_at', $this->year)
            ->latest()
            ->get();

        $this->totalRevenue  = round($this->sales->sum('total'), 2);
        $this->totalVat      = round($this->sales->sum('vat_amount'), 2);
        $this->totalDiscount = round($this->sales->sum('discount_amount'), 2);
        $this->generated     = true;

        Notification::make()->title('Report generated for ' . $this->year . '-' . $this->month)->success()->send();
    }

    protected function getHeaderActions(): array
    {
        $label = $this->year . '-' . $this->month;

        return [
            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->visible(fn () => $this->generated && $this->sales->isNotEmpty())
                ->action(function () use ($label) {
                    $title = 'Monthly Sales Report — ' . $label;
                    return Excel::download(
                        new SalesReportExport($this->sales, $title, auth()->user()->name),
                        'monthly-sales-' . $label . '.xlsx'
                    );
                }),

            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->visible(fn () => $this->generated && $this->sales->isNotEmpty())
                ->action(function () use ($label) {
                    $data = [
                        'title'          => 'Monthly Sales Report — ' . $label,
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
                        'monthly-sales-' . $label . '.pdf'
                    );
                }),
        ];
    }
}
