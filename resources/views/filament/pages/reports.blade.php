<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Introduction Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                Monthly Report Generation
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Generate comprehensive monthly reports for sales, purchases, stock valuation, and low stock alerts.
                Reports are generated in the background and you will receive a notification when they are ready.
            </p>
        </div>

        <!-- Available Reports Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Sales Report Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-blue-500">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Sales Report</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Comprehensive sales data including customer orders, products sold, quantities, and revenue.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Purchase Report Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-green-500">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Purchase Report</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Detailed purchase data including suppliers, products received, quantities, and costs.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Stock Valuation Report Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-purple-500">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Stock Valuation Report</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Current inventory value based on purchase prices and stock quantities.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Low Stock Report Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-red-500">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Low Stock Report</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Products with stock levels below minimum threshold requiring replenishment.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions Section -->
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                        How to Generate Reports
                    </h3>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                        <ol class="list-decimal list-inside space-y-1">
                            <li>Click the "Generate Monthly Report" button in the top right corner</li>
                            <li>Select the type of report you want to generate</li>
                            <li>Choose the month for the report</li>
                            <li>Click "Generate Report" to start the process</li>
                            <li>You will receive a notification when the report is ready</li>
                            <li>Download the report from the notification or check your storage</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Queue Status Info -->
        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-6">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                Background Processing
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Reports are generated in the background using Laravel's queue system. This ensures that large reports
                don't slow down your workflow. Make sure the queue worker is running for reports to be processed.
            </p>
            <div class="mt-3">
                <code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">
                    php artisan queue:work
                </code>
            </div>
        </div>
    </div>
</x-filament-panels::page>
