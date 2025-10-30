<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Form --}}
        <x-filament::section>
            <x-slot name="heading">
                Filter Options
            </x-slot>

            <form wire:submit="generate">
                {{ $this->form }}
            </form>
        </x-filament::section>

        {{-- Summary Statistics --}}
        @if ($purchaseData->isNotEmpty())
            <x-filament::section>
                <x-slot name="heading">
                    Purchase Summary
                </x-slot>

                <x-slot name="description">
                    Period: {{ \Carbon\Carbon::parse($data['startDate'])->format('d M Y') }} -
                    {{ \Carbon\Carbon::parse($data['endDate'])->format('d M Y') }}
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div
                        class="p-6 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-200 dark:border-primary-800">
                        <p class="text-sm text-primary-600 dark:text-primary-400 font-medium">Total Transactions</p>
                        <p class="text-3xl font-bold text-primary-700 dark:text-primary-300 mt-2">
                            {{ number_format($totalTransactions) }}</p>
                    </div>
                    <div
                        class="p-6 bg-info-50 dark:bg-info-900/20 rounded-lg border border-info-200 dark:border-info-800">
                        <p class="text-sm text-info-600 dark:text-info-400 font-medium">Total Items Purchased</p>
                        <p class="text-3xl font-bold text-info-700 dark:text-info-300 mt-2">
                            {{ number_format($purchaseData->count()) }}</p>
                    </div>
                    <div
                        class="p-6 bg-success-50 dark:bg-success-900/20 rounded-lg border border-success-200 dark:border-success-800">
                        <p class="text-sm text-success-600 dark:text-success-400 font-medium">Total Quantity Received
                        </p>
                        <p class="text-3xl font-bold text-success-700 dark:text-success-300 mt-2">
                            {{ number_format($totalQuantity) }}</p>
                    </div>
                    <div
                        class="p-6 bg-warning-50 dark:bg-warning-900/20 rounded-lg border border-warning-200 dark:border-warning-800">
                        <p class="text-sm text-warning-600 dark:text-warning-400 font-medium">Total Purchase Value</p>
                        <p class="text-3xl font-bold text-warning-700 dark:text-warning-300 mt-2">
                            Rp {{ number_format($totalValue, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </x-filament::section>

            {{-- Purchase Data Table --}}
            <x-filament::section>
                <x-slot name="heading">
                    Purchase Details
                </x-slot>

                <x-slot name="description">
                    Detailed breakdown of all purchase transactions
                </x-slot>

                <div class="overflow-hidden">
                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Date</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Inbound #</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    PO #</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Supplier</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Product</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Ordered</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Received</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Unit Price</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Total Value</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($purchaseData as $item)
                                <tr
                                    class="hover:bg-gray-50 dark:hover:bg-gray-800 {{ $item['ordered_quantity'] != $item['received_quantity'] ? 'bg-yellow-50 dark:bg-yellow-900/10' : '' }}">
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ \Carbon\Carbon::parse($item['date'])->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $item['inbound_number'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $item['po_number'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $item['supplier_name'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $item['product_name'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 text-right">
                                        {{ number_format($item['ordered_quantity']) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 text-right">
                                        @if ($item['ordered_quantity'] != $item['received_quantity'])
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                                {{ number_format($item['received_quantity']) }}
                                            </span>
                                        @else
                                            {{ number_format($item['received_quantity']) }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 text-right">
                                        Rp {{ number_format($item['unit_price'], 0, ',', '.') }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100 text-right">
                                        Rp {{ number_format($item['total_value'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="bg-gray-100 dark:bg-gray-800 font-bold">
                                <td colspan="6"
                                    class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 text-right">TOTAL:</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 text-right">
                                    {{ number_format($totalQuantity) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 text-right"></td>
                                <td class="px-4 py-3 text-sm text-warning-700 dark:text-warning-300 text-right">
                                    Rp {{ number_format($totalValue, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            {{-- Additional Insights --}}
            <x-filament::section>
                <x-slot name="heading">
                    Insights
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Top 5 Products by Quantity</h4>
                        <ul class="space-y-2">
                            @foreach ($purchaseData->groupBy('product_name')->map(fn($items) => ['name' => $items->first()['product_name'], 'qty' => $items->sum('received_quantity')])->sortByDesc('qty')->take(5) as $product)
                                <li class="flex justify-between text-sm">
                                    <span class="text-gray-700 dark:text-gray-300">{{ $product['name'] }}</span>
                                    <span
                                        class="font-semibold text-gray-900 dark:text-white">{{ number_format($product['qty']) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Top 5 Suppliers by Value</h4>
                        <ul class="space-y-2">
                            @foreach ($purchaseData->groupBy('supplier_name')->map(fn($items) => ['name' => $items->first()['supplier_name'], 'value' => $items->sum('total_value')])->sortByDesc('value')->take(5) as $supplier)
                                <li class="flex justify-between text-sm">
                                    <span class="text-gray-700 dark:text-gray-300">{{ $supplier['name'] }}</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        Rp {{ number_format($supplier['value'], 0, ',', '.') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                @php
                    $discrepancies = $purchaseData->filter(
                        fn($item) => $item['ordered_quantity'] != $item['received_quantity'],
                    );
                @endphp

                @if ($discrepancies->isNotEmpty())
                    <div
                        class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                        <h4 class="font-semibold text-yellow-800 dark:text-yellow-300 mb-2">⚠ Quantity Discrepancies
                        </h4>
                        <p class="text-sm text-yellow-700 dark:text-yellow-400">
                            {{ $discrepancies->count() }} item(s) have differences between ordered and received
                            quantities (highlighted in yellow above).
                        </p>
                    </div>
                @endif
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
