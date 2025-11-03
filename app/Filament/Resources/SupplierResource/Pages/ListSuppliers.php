<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use App\Services\DocumentGenerationService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSuppliers extends ListRecords
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('exportPdf')
                ->label('Cetak PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $suppliers = $this->getTable()->getRecords();

                    $documentService = app(DocumentGenerationService::class);
                    $pdfContent = $documentService->generateSupplierList($suppliers);

                    return response()->streamDownload(function () use ($pdfContent) {
                        echo $pdfContent;
                    }, 'supplier-list-' . now()->format('Y-m-d-H-i-s') . '.pdf', [
                        'Content-Type' => 'application/pdf',
                    ]);
                }),
        ];
    }
}
