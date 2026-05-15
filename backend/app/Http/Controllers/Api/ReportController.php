<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;
use App\Exports\StockReportExport;

class ReportController extends Controller
{
    /**
     * Return JSON summary for the report page.
     * type: daily | monthly | stock
     * format: json | pdf | excel
     */
    public function generate(Request $request)
    {
        $request->validate([
            'type'   => 'required|in:daily,monthly,stock',
            'format' => 'required|in:json,pdf,excel',
            'date'   => 'nullable|date',
            'month'  => 'nullable|string', // e.g. 2026-05
        ]);

        $user   = $request->user();
        $type   = $request->type;
        $format = $request->format;

        if ($type === 'stock') {
            return $this->stockReport($format);
        }

        return $this->salesReport($request, $user, $type, $format);
    }

    // ── Sales Report (daily / monthly) ──────────────────────────────────────

    private function salesReport(Request $request, $user, string $type, string $format)
    {
        $query = Sale::with(['user:id,name', 'items.product:id,name,sku'])->latest();

        // Scope to own sales for non-managers
        if (! $user->hasRole('manager')) {
            $query->where('user_id', $user->id);
        }

        if ($type === 'daily') {
            $date = $request->date ?? now()->toDateString();
            $query->whereDate('created_at', $date);
            $title    = 'Daily Sales Report — ' . $date;
            $filename = 'daily-sales-' . $date;
        } else {
            $month = $request->month ?? now()->format('Y-m');
            [$year, $mon] = explode('-', $month);
            $query->whereYear('created_at', $year)->whereMonth('created_at', $mon);
            $title    = 'Monthly Sales Report — ' . $month;
            $filename = 'monthly-sales-' . $month;
        }

        $sales = $query->get();

        $summary = [
            'title'          => $title,
            'total_sales'    => $sales->count(),
            'total_revenue'  => round($sales->sum('total'), 2),
            'total_vat'      => round($sales->sum('vat_amount'), 2),
            'total_discount' => round($sales->sum('discount_amount'), 2),
            'prepared_by'    => $user->name,
            'generated_at'   => now()->toDateTimeString(),
            'sales'          => $sales,
        ];

        if ($format === 'json') {
            return response()->json($summary);
        }

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.sales', $summary)->setPaper('a4', 'landscape');
            return $pdf->download($filename . '.pdf');
        }

        // Excel
        return Excel::download(new SalesReportExport($sales, $title, $user->name), $filename . '.xlsx');
    }

    // ── Stock Report ─────────────────────────────────────────────────────────

    private function stockReport(string $format)
    {
        $user     = request()->user();
        $products = Product::orderBy('name')->get();

        $summary = [
            'title'        => 'Stock Report',
            'prepared_by'  => $user->name,
            'generated_at' => now()->toDateTimeString(),
            'total_products'    => $products->count(),
            'out_of_stock'      => $products->where('stock_quantity', 0)->count(),
            'low_stock'         => $products->where('stock_quantity', '>', 0)->where('stock_quantity', '<', 5)->count(),
            'products'          => $products,
        ];

        if ($format === 'json') {
            return response()->json($summary);
        }

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.stock', $summary)->setPaper('a4', 'portrait');
            return $pdf->download('stock-report-' . now()->toDateString() . '.pdf');
        }

        return Excel::download(new StockReportExport($products, $user->name), 'stock-report-' . now()->toDateString() . '.xlsx');
    }
}
