<x-filament-panels::page>

    {{-- Filter Form --}}
    <x-filament::section>
        <form wire:submit="generate">
            {{ $this->form }}
            <div class="mt-4">
                <x-filament::button type="submit" icon="heroicon-o-magnifying-glass">
                    Generate Report
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    @if($generated)

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 mt-4">
        <x-filament::section>
            <p class="text-xs text-gray-500 uppercase font-semibold">Total Sales</p>
            <p class="text-3xl font-bold mt-1">{{ $sales->count() }}</p>
        </x-filament::section>
        <x-filament::section>
            <p class="text-xs text-gray-500 uppercase font-semibold">Total Revenue</p>
            <p class="text-3xl font-bold mt-1 text-green-600">ETB {{ number_format($totalRevenue, 2) }}</p>
        </x-filament::section>
        <x-filament::section>
            <p class="text-xs text-gray-500 uppercase font-semibold">Total VAT (15%)</p>
            <p class="text-3xl font-bold mt-1">ETB {{ number_format($totalVat, 2) }}</p>
        </x-filament::section>
        <x-filament::section>
            <p class="text-xs text-gray-500 uppercase font-semibold">Total Discount</p>
            <p class="text-3xl font-bold mt-1">ETB {{ number_format($totalDiscount, 2) }}</p>
        </x-filament::section>
    </div>

    {{-- Sales Table --}}
    <x-filament::section class="mt-4">
        <x-slot name="heading">Sales on {{ $date }}</x-slot>

        @if($sales->isEmpty())
            <p class="text-gray-400 text-center py-8">No sales found for this date.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">#</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">Sales Officer</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">Items</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">Subtotal</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">Discount</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">VAT</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">Total</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">Status</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                    <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="py-3 px-4">#{{ $sale->id }}</td>
                        <td class="py-3 px-4">{{ $sale->user?->name ?? '—' }}</td>
                        <td class="py-3 px-4">{{ $sale->items->count() }}</td>
                        <td class="py-3 px-4">ETB {{ number_format($sale->subtotal, 2) }}</td>
                        <td class="py-3 px-4">{{ $sale->discount }}%</td>
                        <td class="py-3 px-4">ETB {{ number_format($sale->vat_amount, 2) }}</td>
                        <td class="py-3 px-4 font-bold">ETB {{ number_format($sale->total, 2) }}</td>
                        <td class="py-3 px-4">
                            <span @class([
                                'px-2 py-1 rounded-full text-xs font-semibold',
                                'bg-green-100 text-green-700'  => $sale->status === 'completed',
                                'bg-yellow-100 text-yellow-700' => $sale->status === 'pending_approval',
                                'bg-blue-100 text-blue-700'    => $sale->status === 'approved',
                                'bg-red-100 text-red-700'      => $sale->status === 'rejected',
                            ])>
                                {{ str_replace('_', ' ', $sale->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-gray-500">{{ $sale->created_at->format('H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </x-filament::section>

    @endif

</x-filament-panels::page>
