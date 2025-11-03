<?php

namespace App\Filament\Resources\DriverResource\Pages;

use App\Filament\Resources\DriverResource;
use App\Services\DocumentGenerationService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDrivers extends ListRecords
{
    protected static string $resource = DriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('exportPdf')
                ->label('Cetak PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $drivers = $this->getTable()->getRecords();

                    $documentService = app(DocumentGenerationService::class);
                    $pdfContent = $documentService->generateDriverList($drivers);

                    return response()->streamDownload(function () use ($pdfContent) {
                        echo $pdfContent;
                    }, 'driver-list-' . now()->format('Y-m-d-H-i-s') . '.pdf', [
                        'Content-Type' => 'application/pdf',
                    ]);
                }),
        ];
    }
}
