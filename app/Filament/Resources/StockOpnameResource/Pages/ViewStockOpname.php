<?php

namespace App\Filament\Resources\StockOpnameResource\Pages;

use App\Filament\Resources\StockOpnameResource;
use App\Services\StockMovementService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class ViewStockOpname extends ViewRecord
{
    protected static string $resource = StockOpnameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('confirm')
                ->label('Confirm Opname')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Confirm Stock Opname')
                ->modalDescription('This will adjust stock levels based on the physical count. This action cannot be undone.')
                ->modalSubmitActionLabel('Confirm')
                ->visible(fn (): bool => !$this->record->stockMovements()->exists())
                ->action(function () {
                    try {
                        DB::transaction(function () {
                            $stockService = app(StockMovementService::class);
                            
                            foreach ($this->record->items as $item) {
                                if ($item->variance != 0) {
                                    $stockService->recordAdjustment(
                                        $this->record,
                                        $item->product,
                                        $item->variance
                                    );
                                }
                            }
                        });
                        
                        Notification::make()
                            ->title('Stock Opname Confirmed')
                            ->body('Stock adjustments have been recorded successfully.')
                            ->success()
                            ->send();
                            
                        return redirect($this->getResource()::getUrl('index'));
                            
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error Confirming Stock Opname')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\EditAction::make()
                ->visible(fn (): bool => !$this->record->stockMovements()->exists()),
            Actions\DeleteAction::make()
                ->visible(fn (): bool => !$this->record->stockMovements()->exists()),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Stock Opname Information')
                    ->schema([
                        Forms\Components\Placeholder::make('opname_number')
                            ->label('Opname Number')
                            ->content(fn (): string => $this->record->opname_number),
                        Forms\Components\Placeholder::make('opname_date')
                            ->label('Opname Date')
                            ->content(fn (): string => $this->record->opname_date->format('d M Y')),
                        Forms\Components\Placeholder::make('creator')
                            ->label('Created By')
                            ->content(fn (): string => $this->record->creator->name ?? 'N/A'),
                        Forms\Components\Placeholder::make('notes')
                            ->label('Notes')
                            ->content(fn (): string => $this->record->notes ?? 'No notes')
                            ->columnSpanFull(),
                        Forms\Components\Placeholder::make('status')
                            ->label('Status')
                            ->content(fn (): string => 
                                $this->record->stockMovements()->exists() ? '✓ Confirmed' : '⏳ Pending'
                            ),
                    ])
                    ->columns(3),

                Section::make('Stock Count Details')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->label('')
                            ->relationship()
                            ->schema([
                                Forms\Components\Placeholder::make('product_name')
                                    ->label('Product')
                                    ->content(fn ($record): string => $record->product->name),
                                Forms\Components\Placeholder::make('product_sku')
                                    ->label('SKU')
                                    ->content(fn ($record): string => $record->product->sku),
                                Forms\Components\Placeholder::make('system_stock')
                                    ->label('System Stock')
                                    ->content(fn ($record): string => 
                                        $record->system_stock . ' ' . $record->product->unit
                                    ),
                                Forms\Components\Placeholder::make('physical_stock')
                                    ->label('Physical Stock')
                                    ->content(fn ($record): string => 
                                        $record->physical_stock . ' ' . $record->product->unit
                                    ),
                                Forms\Components\Placeholder::make('variance')
                                    ->label('Variance')
                                    ->content(fn ($record): string => 
                                        ($record->variance > 0 ? '+' : '') . $record->variance . ' ' . $record->product->unit
                                    ),
                            ])
                            ->columns(5)
                            ->disabled()
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
