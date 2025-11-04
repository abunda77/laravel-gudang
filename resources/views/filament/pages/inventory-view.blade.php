<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Section --}}
        {{-- Stats Overview --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-header flex items-center gap-x-3 overflow-hidden px-6 py-4">
                <div class="grid flex-1 gap-y-1">
                    <div class="flex items-center gap-x-3">
                        <span class="text-2xl">📦</span>
                        <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                            Real-Time Inventory Overview
                        </h3>
                        <x-filament::badge color="success" size="sm">
                            <span class="flex items-center gap-x-1">
                                <span class="relative flex h-1.5 w-1.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-success-500"></span>
                                </span>
                                Live
                            </span>
                        </x-filament::badge>
                    </div>
                    <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400">
                        Live stock monitoring with automatic calculations
                    </p>
                </div>
            </div>

            <div class="fi-section-content-ctn border-t border-gray-200 dark:border-white/10">
                <div class="fi-section-content p-6">
                    <dl class="grid gap-4">
                        {{-- Total Products --}}
                        <div class="flex items-center justify-between rounded-lg border border-gray-200 dark:border-white/10 p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition">
                            <div class="flex items-center gap-x-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-500/10">
                                    <span class="text-xl">📊</span>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Products</dt>
                                    <dd class="text-xs text-gray-500 dark:text-gray-400">Active items in inventory</dd>
                                </div>
                            </div>
                            <div class="text-right">
                                <dd class="text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ number_format(\App\Models\Product::count()) }}
                                </dd>
                            </div>
                        </div>

                        {{-- Low Stock Alert --}}
                        <div class="flex items-center justify-between rounded-lg border border-gray-200 dark:border-white/10 p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition">
                            <div class="flex items-center gap-x-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-warning-50 dark:bg-warning-500/10">
                                    <span class="text-xl">⚠️</span>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Low Stock Alert</dt>
                                    <dd class="text-xs text-gray-500 dark:text-gray-400">Items below minimum stock</dd>
                                </div>
                            </div>
                            <div class="text-right">
                                <dd class="text-3xl font-bold text-warning-600 dark:text-warning-400">
                                    {{ number_format(\App\Models\Product::whereRaw('(SELECT COALESCE(SUM(quantity), 0) FROM stock_movements WHERE stock_movements.product_id = products.id) < products.minimum_stock')->count()) }}
                                </dd>
                            </div>
                        </div>

                        {{-- Total Stock Value --}}
                        <div class="flex items-center justify-between rounded-lg border border-gray-200 dark:border-white/10 p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition">
                            <div class="flex items-center gap-x-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-success-50 dark:bg-success-500/10">
                                    <span class="text-xl">💰</span>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Stock Value</dt>
                                    <dd class="text-xs text-gray-500 dark:text-gray-400">Current inventory valuation</dd>
                                </div>
                            </div>
                            <div class="text-right">
                                <dd class="text-3xl font-bold text-success-600 dark:text-success-400">
                                    Rp {{ number_format(
                                        \App\Models\Product::all()->sum(function ($product) {
                                            return app(\App\Services\StockMovementService::class)->getCurrentStock($product) * $product->purchase_price;
                                        }),
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </dd>
                            </div>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Table Section --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
