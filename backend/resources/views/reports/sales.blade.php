<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; padding: 20px; }
  .header { border-bottom: 3px solid #1a5c32; padding-bottom: 12px; margin-bottom: 16px; }
  .header h1 { font-size: 18px; color: #1a5c32; }
  .header p  { font-size: 10px; color: #555; margin-top: 3px; }
  .meta { display: flex; gap: 40px; margin-bottom: 16px; }
  .meta-item label { font-size: 9px; color: #888; text-transform: uppercase; }
  .meta-item span  { font-size: 12px; font-weight: bold; display: block; }
  .summary { background: #f0f7f3; border: 1px solid #c3e0ce; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; display: flex; gap: 30px; }
  .sum-item label { font-size: 9px; color: #555; text-transform: uppercase; }
  .sum-item span  { font-size: 13px; font-weight: bold; color: #1a5c32; display: block; }
  table { width: 100%; border-collapse: collapse; font-size: 10px; }
  thead th { background: #1a5c32; color: #fff; padding: 7px 8px; text-align: left; }
  tbody td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
  tbody tr:nth-child(even) { background: #f9fafb; }
  .status-completed       { color: #065f46; font-weight: 600; }
  .status-pending_approval { color: #92400e; font-weight: 600; }
  .status-approved        { color: #1e40af; font-weight: 600; }
  .status-rejected        { color: #991b1b; font-weight: 600; }
  .footer { margin-top: 20px; font-size: 9px; color: #888; border-top: 1px solid #e5e7eb; padding-top: 8px; display: flex; justify-content: space-between; }
</style>
</head>
<body>

<div class="header">
  <h1>{{ $title }}</h1>
  <p>Semre Inventory &amp; Sales Management System</p>
</div>

<div class="summary">
  <div class="sum-item"><label>Total Sales</label><span>{{ $total_sales }}</span></div>
  <div class="sum-item"><label>Total Revenue</label><span>{{ number_format($total_revenue, 2) }} Birr</span></div>
  <div class="sum-item"><label>Total VAT</label><span>{{ number_format($total_vat, 2) }} Birr</span></div>
  <div class="sum-item"><label>Total Discount</label><span>{{ number_format($total_discount, 2) }} Birr</span></div>
</div>

<table>
  <thead>
    <tr>
      <th>#</th>
      <th>Cashier</th>
      <th>Items</th>
      <th>Subtotal</th>
      <th>Discount</th>
      <th>VAT (15%)</th>
      <th>Total (Birr)</th>
      <th>Status</th>
      <th>Date</th>
    </tr>
  </thead>
  <tbody>
    @forelse($sales as $sale)
    <tr>
      <td>#{{ $sale->id }}</td>
      <td>{{ $sale->user?->name ?? '—' }}</td>
      <td>{{ $sale->items->count() }}</td>
      <td>{{ number_format($sale->subtotal, 2) }}</td>
      <td>{{ $sale->discount }}% ({{ number_format($sale->discount_amount, 2) }})</td>
      <td>{{ number_format($sale->vat_amount, 2) }}</td>
      <td><strong>{{ number_format($sale->total, 2) }}</strong></td>
      <td class="status-{{ $sale->status }}">{{ str_replace('_', ' ', $sale->status) }}</td>
      <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
    </tr>
    @empty
    <tr><td colspan="9" style="text-align:center; padding:16px; color:#888;">No sales found for this period.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="footer">
  <span>Prepared by: {{ $prepared_by }}</span>
  <span>Generated: {{ $generated_at }}</span>
</div>

</body>
</html>
