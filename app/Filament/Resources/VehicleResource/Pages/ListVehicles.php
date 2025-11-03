<?php

namespace App\Filament\Resources\VehicleResource\Pages;

use App\Filament\Resources\VehicleResource;
use App\Services\DocumentGenerationService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVehicles extends ListRecords
{
    protected static string $resource = VehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('exportPdf')
                ->label('Cetak PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $vehicles = $this->getTable()->getRecords();

                    $documentService = app(DocumentGenerationService::class);
                    $pdfContent = $documentService->generateVehicleList($vehicles);

                    return response()->streamDownload(function () use ($pdfContent) {
                        echo $pdfContent;
                    }, 'vehicle-list-' . now()->format('Y-m-d-H-i-s') . '.pdf', [
                        'Content-Type' => 'application/pdf',
                    ]);
                }),
        ];
    }
}
