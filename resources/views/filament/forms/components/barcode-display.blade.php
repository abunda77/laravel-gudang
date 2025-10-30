<div class="fi-fo-placeholder">
    @if ($getRecord() && $getRecord()->barcode)
        <div class="flex flex-col items-center gap-2 p-4 bg-white rounded-lg border border-gray-200">
            <div class="text-sm font-medium text-gray-700">
                Delivery Order Barcode
            </div>
            <div class="flex justify-center">
                <img src="data:image/svg+xml;base64,{{ $getRecord()->barcode }}"
                    alt="Barcode for {{ $getRecord()->do_number }}" class="max-w-full h-auto">
            </div>
        </div>
    @else
        <div class="text-sm text-gray-500">
            Barcode will be generated when the delivery order is saved.
        </div>
    @endif
</div>
