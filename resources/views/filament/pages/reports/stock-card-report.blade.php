<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Form --}}
        <x-filament::section>
            <x-slot name="heading">
                Filter Options
            </x-slot>

            <form wire:submit="generate">
                {{ $this->form }}

                <div class="mt-6 flex gap-3">
                    <x-filament::button type="submit">
                        Generate Report
                    </x-filament::button>

                    @if ($movements->isNotEmpty())
                        <x-filament::button color="success" wire:click="exportPdf" type="button">
                            Export to PDF
                        </x-filament::button>
                    @endif
                </div>
            </form>
        </x-filament::section>

        {{-- Report Results --}}
        @if ($movements->isNotEmpty() && $selectedProduct)
            <x-filament::section>
                <x-slot name="heading">
                    Stock Card: {{ $selectedProduct->name }} ({{ $selectedProduct->sku }})
                </x-slot>

                <x-slot name="description">
                    @if ($startDate && $endDate)
                        Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} -
                        {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                    @elseif($startDate)
                        From: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                    @elseif($endDate)
                        Until: {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                    @else
                        All Time
                    @endif
                </x-slot>

                <div class="overflow-hidden">
                    {{ $this->table }}
                </div>

                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Movements</p>
                            <p class="text-2xl font-bold">{{ $movements->count() }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Final Stock</p>
                            <p class="text-2xl font-bold text-primary-600">
                                {{ $movements->last()->running_balance ?? 0 }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Stock Value</p>
                            <p class="text-2xl font-bold text-success-600">
                                Rp
                                {{ number_format(($movements->last()->running_balance ?? 0) * $selectedProduct->purchase_price, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
