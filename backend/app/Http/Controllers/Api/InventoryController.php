<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockInRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class InventoryController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::select('id', 'name', 'sku', 'category', 'stock_quantity')
            ->latest()
            ->paginate(20);

        return response()->json($products);
    }

    public function stockIn(StockInRequest $request, Product $product): JsonResponse
    {
        $product->increment('stock_quantity', $request->quantity);

        return response()->json([
            'message'        => 'Stock added successfully.',
            'product_id'     => $product->id,
            'sku'            => $product->sku,
            'added'          => $request->quantity,
            'stock_quantity' => $product->fresh()->stock_quantity,
        ]);
    }
}
