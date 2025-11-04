<x-filament-panels::page>
    <div class="space-y-8">
        <!-- Hero Section -->
        <div
            class="relative overflow-hidden bg-gradient-to-br from-primary-50 to-primary-100 dark:from-gray-800 dark:to-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
            <div class="relative px-8 py-10">
                <div class="flex items-center gap-4 mb-4">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-primary-500 rounded-xl shadow-lg">
                            <x-filament::icon icon="heroicon-o-document-chart-bar" class="h-8 w-8 text-white" />
                        </div>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Monthly Report Generation
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Generate comprehensive reports with automated background processing
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Reports Section -->
        <div>
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Available Reports</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Select from the following report types to generate detailed insights
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Sales Report Card -->
                <div
                    class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-blue-400 to-blue-600"></div>
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div
                                    class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg group-hover:scale-110 transition-transform duration-300">
                                    <x-filament::icon icon="heroicon-o-chart-bar"
                                        class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                                    Sales Report
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                    Comprehensive sales data including customer orders, products sold, quantities, and
                                    revenue analysis.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purchase Report Card -->
                <div
                    class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-green-400 to-green-600"></div>
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div
                                    class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg group-hover:scale-110 transition-transform duration-300">
                                    <x-filament::icon icon="heroicon-o-shopping-cart"
                                        class="h-6 w-6 text-green-600 dark:text-green-400" />
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                                    Purchase Report
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                    Detailed purchase data including suppliers, products received, quantities, and cost
                                    analysis.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Valuation Report Card -->
                <div
                    class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-purple-400 to-purple-600"></div>
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div
                                    class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg group-hover:scale-110 transition-transform duration-300">
                                    <x-filament::icon icon="heroicon-o-currency-dollar"
                                        class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                                    Stock Valuation Report
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                    Current inventory value based on purchase prices and stock quantities with detailed
                                    breakdown.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Report Card -->
                <div
                    class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-red-400 to-red-600"></div>
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div
                                    class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg group-hover:scale-110 transition-transform duration-300">
                                    <x-filament::icon icon="heroicon-o-exclamation-triangle"
                                        class="h-6 w-6 text-red-600 dark:text-red-400" />
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                                    Low Stock Report
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                    Products with stock levels below minimum threshold requiring immediate
                                    replenishment.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions Section -->
        <div
            class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 rounded-xl border border-blue-200 dark:border-blue-800 overflow-hidden">
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <x-filament::icon icon="heroicon-o-information-circle"
                                class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-blue-900 dark:text-blue-100 mb-3">
                            How to Generate Reports
                        </h3>
                        <div class="space-y-2">
                            <div class="flex items-start gap-3">
                                <span
                                    class="flex-shrink-0 flex items-center justify-center w-6 h-6 text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-200 dark:bg-blue-800 rounded-full">1</span>
                                <p class="text-sm text-blue-800 dark:text-blue-200 pt-0.5">Click the "Generate Monthly
                                    Report" button in the top right corner</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span
                                    class="flex-shrink-0 flex items-center justify-center w-6 h-6 text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-200 dark:bg-blue-800 rounded-full">2</span>
                                <p class="text-sm text-blue-800 dark:text-blue-200 pt-0.5">Select the type of report you
                                    want to generate</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span
                                    class="flex-shrink-0 flex items-center justify-center w-6 h-6 text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-200 dark:bg-blue-800 rounded-full">3</span>
                                <p class="text-sm text-blue-800 dark:text-blue-200 pt-0.5">Choose the month for the
                                    report</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span
                                    class="flex-shrink-0 flex items-center justify-center w-6 h-6 text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-200 dark:bg-blue-800 rounded-full">4</span>
                                <p class="text-sm text-blue-800 dark:text-blue-200 pt-0.5">Click "Generate Report" and
                                    wait for notification when ready</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Generated Reports History -->
        <div>
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Generated Reports History</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    View and download previously generated reports
                </p>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                {{ $this->table }}
            </div>
        </div>

        <!-- Technical Info Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
                            <x-filament::icon icon="heroicon-o-cog-6-tooth"
                                class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                            Background Processing
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-3">
                            Reports are generated asynchronously using Laravel's queue system. This ensures that large
                            reports don't slow down your workflow. Make sure the queue worker is running for reports to
                            be processed.
                        </p>
                        <div
                            class="inline-flex items-center gap-2 px-3 py-2 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                            <x-filament::icon icon="heroicon-o-command-line"
                                class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                            <code class="text-xs font-mono text-gray-700 dark:text-gray-300">
                                php artisan queue:work
                            </code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
