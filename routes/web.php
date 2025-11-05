<?php

use App\Models\DeliveryOrder;
use App\Services\DocumentGenerationService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Delivery Order Print Route
Route::get('/delivery-orders/{deliveryOrder}/print', function (DeliveryOrder $deliveryOrder) {
    $documentService = app(DocumentGenerationService::class);
    $pdf = $documentService->generateDeliveryOrder($deliveryOrder);
    
    return response($pdf, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="Surat-Jalan-' . $deliveryOrder->do_number . '.pdf"',
    ]);
})->name('delivery-orders.print')->middleware(['auth']);
