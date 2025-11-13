<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Reset Data Transaksi
            </x-slot>

            <x-slot name="description">
                Hapus semua data transaksi dari sistem. Data master (produk, kategori, varian, customer, supplier, driver, kendaraan) dan user akan tetap tersimpan.
            </x-slot>

            <div class="space-y-4">
                <div class="rounded-lg bg-warning-50 p-4 dark:bg-warning-900/20">
                    <div class="flex">
                       
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-warning-800 dark:text-warning-200">
                                Peringatan
                            </h3>
                            <div class="mt-2 text-sm text-warning-700 dark:text-warning-300">
                                <p>Tindakan ini akan menghapus:</p>
                                <ul class="list-disc list-inside mt-2 space-y-1">
                                    <li>Semua Purchase Order</li>
                                    <li>Semua Sales Order</li>
                                    <li>Semua Inbound Operations</li>
                                    <li>Semua Outbound Operations</li>
                                    <li>Semua Delivery Orders</li>
                                    <li>Semua Invoices</li>
                                    <li>Semua Stock Opnames</li>
                                    <li>Semua Stock Movements</li>
                                    <li>Semua Generated Reports</li>
                                </ul>
                                <p class="mt-3 font-semibold">Yang TIDAK akan dihapus:</p>
                                <ul class="list-disc list-inside mt-2 space-y-1">
                                    <li>Data User</li>
                                    <li>Data Produk, Kategori, dan Varian</li>
                                    <li>Data Customer</li>
                                    <li>Data Supplier</li>
                                    <li>Data Driver</li>
                                    <li>Data Kendaraan</li>
                                </ul>
                                <p class="mt-3 font-semibold text-danger-600 dark:text-danger-400">
                                    Hasil akhir: Semua stok produk akan menjadi 0
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-danger-50 p-4 dark:bg-danger-900/20">
                    <div class="flex">
                        
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-danger-800 dark:text-danger-200">
                                Tindakan Tidak Dapat Dibatalkan
                            </h3>
                            <div class="mt-2 text-sm text-danger-700 dark:text-danger-300">
                                <p>Data yang dihapus tidak dapat dikembalikan. Pastikan Anda telah membuat backup database sebelum melanjutkan.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <x-filament::button
                        color="danger"
                        wire:click="resetTransactions"
                        wire:confirm="Apakah Anda yakin ingin menghapus SEMUA data transaksi? Tindakan ini tidak dapat dibatalkan!"
                    >
                        <x-heroicon-o-arrow-path class="w-5 h-5 mr-2" />
                        Reset Data Transaksi
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
