<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Real-Time Inventory Overview
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        View current stock levels for all products. Stock is calculated from all movements in real-time.
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        <svg class="w-2 h-2 mr-1.5" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                        Live Updates
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Products</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ \App\Models\Product::count() }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Low Stock Items</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ \App\Models\Product::whereRaw('(SELECT COALESCE(SUM(quantity), 0) FROM stock_movements WHERE stock_movements.product_id = products.id) < products.minimum_stock')->count() }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Stock Value</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                Rp
                                {{ number_format(
                                    \App\Models\Product::all()->sum(function ($product) {
                                        return app(\App\Services\StockMovementService::class)->getCurrentStock($product) * $product->purchase_price;
                                    }),
                                    0,
                                    ',',
                                    '.',
                                ) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
