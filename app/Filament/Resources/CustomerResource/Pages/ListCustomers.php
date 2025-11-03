<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Services\DocumentGenerationService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('exportPdf')
                ->label('Cetak PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $customers = $this->getTable()->getRecords();

                    $documentService = app(DocumentGenerationService::class);
                    $pdfContent = $documentService->generateCustomerList($customers);

                    return response()->streamDownload(function () use ($pdfContent) {
                        echo $pdfContent;
                    }, 'customer-list-' . now()->format('Y-m-d-H-i-s') . '.pdf', [
                        'Content-Type' => 'application/pdf',
                    ]);
                }),
        ];
    }
}
