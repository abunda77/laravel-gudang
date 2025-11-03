<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Services\DocumentGenerationService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Collection;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('exportPdf')
                ->label('Cetak PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $products = $this->getTable()->getRecords();

                    $documentService = app(DocumentGenerationService::class);
                    $pdfContent = $documentService->generateProductList($products);

                    return response()->streamDownload(function () use ($pdfContent) {
                        echo $pdfContent;
                    }, 'product-list-' . now()->format('Y-m-d-H-i-s') . '.pdf', [
                        'Content-Type' => 'application/pdf',
                    ]);
                }),
        ];
    }
}
