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
  .summary { background: #f0f7f3; border: 1px solid #c3e0ce; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; display: flex; gap: 30px; }
  .sum-item label { font-size: 9px; color: #555; text-transform: uppercase; }
  .sum-item span  { font-size: 13px; font-weight: bold; color: #1a5c32; display: block; }
  table { width: 100%; border-collapse: collapse; font-size: 10px; }
  thead th { background: #1a5c32; color: #fff; padding: 7px 8px; text-align: left; }
  tbody td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
  tbody tr:nth-child(even) { background: #f9fafb; }
  .in-stock  { color: #065f46; font-weight: 600; }
  .low-stock { color: #92400e; font-weight: 600; }
  .out-stock { color: #991b1b; font-weight: 600; }
  .footer { margin-top: 20px; font-size: 9px; color: #888; border-top: 1px solid #e5e7eb; padding-top: 8px; display: flex; justify-content: space-between; }
</style>
</head>
<body>

<div class="header">
  <h1>Stock Report</h1>
  <p>Semre Inventory &amp; Sales Management System</p>
</div>

<div class="summary">
  <div class="sum-item"><label>Total Products</label><span>{{ $total_products }}</span></div>
  <div class="sum-item"><label>Out of Stock</label><span>{{ $out_of_stock }}</span></div>
  <div class="sum-item"><label>Low Stock</label><span>{{ $low_stock }}</span></div>
  <div class="sum-item"><label>In Stock</label><span>{{ $total_products - $out_of_stock - $low_stock }}</span></div>
</div>

<table>
  <thead>
    <tr>
      <th>Product Name</th>
      <th>Category</th>
      <th>SKU</th>
      <th>Cost Price (Birr)</th>
      <th>Selling Price (Birr)</th>
      <th>Stock Qty</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    @forelse($products as $product)
    <tr>
      <td><strong>{{ $product->name }}</strong></td>
      <td>{{ $product->category }}</td>
      <td>{{ $product->sku }}</td>
      <td>{{ number_format($product->cost_price, 2) }}</td>
      <td>{{ number_format($product->selling_price, 2) }}</td>
      <td><strong>{{ $product->stock_quantity }}</strong></td>
      <td>
        @if($product->stock_quantity === 0)
          <span class="out-stock">Out of Stock</span>
        @elseif($product->stock_quantity < 5)
          <span class="low-stock">Low Stock</span>
        @else
          <span class="in-stock">In Stock</span>
        @endif
      </td>
    </tr>
    @empty
    <tr><td colspan="7" style="text-align:center; padding:16px; color:#888;">No products found.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="footer">
  <span>Prepared by: {{ $prepared_by }}</span>
  <span>Generated: {{ $generated_at }}</span>
</div>

</body>
</html>
