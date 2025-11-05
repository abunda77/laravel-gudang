<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex items-start">
              
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        Peringatan
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>Tindakan ini akan menghapus <strong>SEMUA DATA</strong> dari sistem kecuali data user.</p>
                        <p class="mt-2">Data yang akan dihapus meliputi:</p>
                        <ul class="list-disc list-inside mt-2 space-y-1">
                            <li>Semua transaksi (Purchase Order, Sales Order, Inbound, Outbound, Delivery Order, Invoice)</li>
                            <li>Semua data master (Produk, Kategori, Varian, Customer, Supplier, Driver, Kendaraan)</li>
                            <li>Semua pergerakan stok dan stock opname</li>
                            <li>Semua laporan yang telah dibuat</li>
                        </ul>
                        <p class="mt-3 font-semibold">Tindakan ini TIDAK DAPAT DIBATALKAN!</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                Reset Data Sistem
            </h2>
            
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                Gunakan fitur ini dengan hati-hati. Pastikan Anda telah membuat backup database sebelum melakukan reset data.
            </p>

            <div class="flex justify-end">
                <x-filament::button
                    color="danger"
                    wire:click="resetAllData"
                    wire:confirm="Apakah Anda yakin ingin menghapus SEMUA DATA? Tindakan ini tidak dapat dibatalkan!"
                >
                    <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                    </svg>
                    Reset Semua Data
                </x-filament::button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
