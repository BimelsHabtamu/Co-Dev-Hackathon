<x-filament-panels::page>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-filament::section>
            <p class="text-xs text-gray-500 uppercase font-semibold">Total Products</p>
            <p class="text-3xl font-bold mt-1">{{ $totalProducts }}</p>
        </x-filament::section>
        <x-filament::section>
            <p class="text-xs text-gray-500 uppercase font-semibold">In Stock</p>
            <p class="text-3xl font-bold mt-1 text-green-600">{{ $inStock }}</p>
        </x-filament::section>
        <x-filament::section>
            <p class="text-xs text-gray-500 uppercase font-semibold">Low Stock</p>
            <p class="text-3xl font-bold mt-1 text-yellow-500">{{ $lowStock }}</p>
        </x-filament::section>
        <x-filament::section>
            <p class="text-xs text-gray-500 uppercase font-semibold">Out of Stock</p>
            <p class="text-3xl font-bold mt-1 text-red-600">{{ $outOfStock }}</p>
        </x-filament::section>
    </div>

    {{-- Products Table --}}
    <x-filament::section class="mt-4">
        <x-slot name="heading">Current Stock Levels</x-slot>

        @if($products->isEmpty())
            <p class="text-gray-400 text-center py-8">No products found.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">Product</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">Category</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">SKU</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">Cost Price</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">Selling Price</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">Stock Qty</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="py-3 px-4 font-semibold">{{ $product->name }}</td>
                        <td class="py-3 px-4">{{ $product->category }}</td>
                        <td class="py-3 px-4">
                            <code class="bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded text-xs">{{ $product->sku }}</code>
                        </td>
                        <td class="py-3 px-4">ETB {{ number_format($product->cost_price, 2) }}</td>
                        <td class="py-3 px-4">ETB {{ number_format($product->selling_price, 2) }}</td>
                        <td class="py-3 px-4 font-bold">{{ $product->stock_quantity }}</td>
                        <td class="py-3 px-4">
                            @if($product->stock_quantity === 0)
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Out of Stock</span>
                            @elseif($product->stock_quantity < 5)
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">Low Stock</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">In Stock</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </x-filament::section>

</x-filament-panels::page>
