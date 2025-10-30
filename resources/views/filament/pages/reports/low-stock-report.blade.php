<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Summary Statistics --}}
        <x-filament::section>
            <x-slot name="heading">
                Low Stock Summary
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                    <p class="text-sm text-red-600 dark:text-red-400 font-medium">Products Below Minimum</p>
                    <p class="text-3xl font-bold text-red-700 dark:text-red-300 mt-2">{{ $lowStockProducts->count() }}
                    </p>
                </div>
                <div
                    class="p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg border border-orange-200 dark:border-orange-800">
                    <p class="text-sm text-orange-600 dark:text-orange-400 font-medium">Total Shortage</p>
                    <p class="text-3xl font-bold text-orange-700 dark:text-orange-300 mt-2">
                        {{ $lowStockProducts->sum('shortage') }}</p>
                </div>
                <div
                    class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                    <p class="text-sm text-yellow-600 dark:text-yellow-400 font-medium">Requires Immediate Action</p>
                    <p class="text-3xl font-bold text-yellow-700 dark:text-yellow-300 mt-2">
                        {{ $lowStockProducts->filter(fn($p) => $p['current_stock'] == 0)->count() }}
                    </p>
                </div>
            </div>
        </x-filament::section>

        {{-- Products Table --}}
        <x-filament::section>
            <x-slot name="heading">
                Low Stock Products
            </x-slot>

            <x-slot name="description">
                Products with current stock below their minimum threshold
            </x-slot>

            <div class="overflow-hidden">
                {{ $this->table }}
            </div>
        </x-filament::section>

        @if ($lowStockProducts->isEmpty())
            <x-filament::section>
                <div class="text-center py-12">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-success-100 dark:bg-success-900/20 mb-4">
                        <svg class="w-8 h-8 text-success-600 dark:text-success-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">All Stock Levels are Good!</h3>
                    <p class="text-gray-600 dark:text-gray-400">No products are currently below their minimum stock
                        threshold.</p>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
