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
        <x-filament::section>
            <x-slot name="heading">
                Inventory Valuation Summary
            </x-slot>

            <x-slot name="description">
                @if ($categoryId)
                    Filtered by: {{ \App\Models\ProductCategory::find($categoryId)?->name }}
                @else
                    All Categories
                @endif
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div
                    class="p-6 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-200 dark:border-primary-800">
                    <p class="text-sm text-primary-600 dark:text-primary-400 font-medium">Total Products</p>
                    <p class="text-3xl font-bold text-primary-700 dark:text-primary-300 mt-2">
                        {{ number_format($totalProducts) }}</p>
                </div>
                <div
                    class="p-6 bg-success-50 dark:bg-success-900/20 rounded-lg border border-success-200 dark:border-success-800">
                    <p class="text-sm text-success-600 dark:text-success-400 font-medium">Total Stock Quantity</p>
                    <p class="text-3xl font-bold text-success-700 dark:text-success-300 mt-2">
                        {{ number_format($products->sum('current_stock')) }}
                    </p>
                </div>
                <div
                    class="p-6 bg-warning-50 dark:bg-warning-900/20 rounded-lg border border-warning-200 dark:border-warning-800">
                    <p class="text-sm text-warning-600 dark:text-warning-400 font-medium">Total Inventory Value</p>
                    <p class="text-3xl font-bold text-warning-700 dark:text-warning-300 mt-2">
                        Rp {{ number_format($totalValue, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </x-filament::section>

        {{-- Products Table --}}
        <x-filament::section>
            <x-slot name="heading">
                Product Valuation Details
            </x-slot>

            <x-slot name="description">
                Detailed breakdown of inventory value by product
            </x-slot>

            <div class="overflow-hidden">
                {{ $this->table }}
            </div>
        </x-filament::section>

        {{-- Additional Insights --}}
        @if ($products->isNotEmpty())
            <x-filament::section>
                <x-slot name="heading">
                    Insights
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Top 5 Most Valuable Products</h4>
                        <ul class="space-y-2">
                            @foreach ($products->sortByDesc('stock_value')->take(5) as $product)
                                <li class="flex justify-between text-sm">
                                    <span class="text-gray-700 dark:text-gray-300">{{ $product['name'] }}</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        Rp {{ number_format($product['stock_value'], 0, ',', '.') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Stock Distribution</h4>
                        <ul class="space-y-2">
                            <li class="flex justify-between text-sm">
                                <span class="text-gray-700 dark:text-gray-300">Products with Stock</span>
                                <span class="font-semibold text-success-600">
                                    {{ $products->filter(fn($p) => $p['current_stock'] > 0)->count() }}
                                </span>
                            </li>
                            <li class="flex justify-between text-sm">
                                <span class="text-gray-700 dark:text-gray-300">Products Out of Stock</span>
                                <span class="font-semibold text-danger-600">
                                    {{ $products->filter(fn($p) => $p['current_stock'] == 0)->count() }}
                                </span>
                            </li>
                            <li class="flex justify-between text-sm">
                                <span class="text-gray-700 dark:text-gray-300">Average Stock Value</span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    Rp
                                    {{ number_format($totalProducts > 0 ? $totalValue / $totalProducts : 0, 0, ',', '.') }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
