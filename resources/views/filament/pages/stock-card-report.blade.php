<x-filament-panels::page>
    @if($product)
        @php
            $productModel = \App\Models\Product::find($product);
        @endphp
        
        @if($productModel)
            <x-filament::section class="mb-6">
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-filament::icon
                            icon="heroicon-o-cube"
                            class="h-5 w-5 text-gray-400"
                        />
                        <span>Product Information</span>
                    </div>
                </x-slot>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400" style="color: #c1c434;">
                            SKU :  {{ $productModel->sku }}
                        </dt>

                    </div>
                    
                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Product Name :  {{ $productModel->name }}
                        </dt>
                       
                    </div>
                    
                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Category : {{ $productModel->category->name ?? '-' }}
                        </dt>
                        <dd class="text-xl font-bold text-gray-900 dark:text-white">
                            
                        </dd>
                    </div>
                    
                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Current Stock : @php
                                $currentStock = app(\App\Services\StockMovementService::class)->getCurrentStock($productModel);
                                $stockColor = $currentStock < $productModel->minimum_stock ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400';
                            @endphp
                            <span class="text-2xl font-bold {{ $stockColor }}">
                                {{ number_format($currentStock) }}
                            </span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ $productModel->unit }}
                        </dt>
                        
                            
                        
                        @if($currentStock < $productModel->minimum_stock)
                            <dd class="text-xs text-red-600 dark:text-red-400 font-medium">
                                ⚠️ Below minimum stock ({{ $productModel->minimum_stock }} {{ $productModel->unit }})
                            </dd>
                        @endif
                    </div>
                </div>
            </x-filament::section>
        @endif
    @endif
    
    {{ $this->table }}
</x-filament-panels::page>
