<?php

namespace App\Filament\Resources\InboundOperationResource\Schemas;

use App\Enums\PurchaseOrderStatus;
use App\Models\InboundOperation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InboundOperationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['purchaseOrder.supplier', 'receiver', 'stockMovements']))
            ->columns([
                Tables\Columns\TextColumn::make('inbound_number')
                    ->label('Inbound Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('purchaseOrder.po_number')
                    ->label('PO Number')
                    ->searchable()
                    ->sortable()
                    ->url(fn (InboundOperation $record): string => route('filament.admin.resources.purchase-orders.edit', ['record' => $record->purchase_order_id])
                    )
                    ->color('info'),

                Tables\Columns\TextColumn::make('purchaseOrder.supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('received_date')
                    ->label('Received Date')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('receiver.name')
                    ->label('Received By')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_confirmed')
                    ->label('Confirmed')
                    ->boolean()
                    ->getStateUsing(function (InboundOperation $record): bool {
                        return $record->stockMovements()->exists();
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('purchase_order')
                    ->relationship('purchaseOrder', 'po_number')
                    ->label('Purchase Order')
                    ->preload()
                    ->searchable(),

                Tables\Filters\Filter::make('received_date')
                    ->form([
                        Forms\Components\DatePicker::make('received_from')
                            ->label('Received From'),
                        Forms\Components\DatePicker::make('received_until')
                            ->label('Received Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['received_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('received_date', '>=', $date),
                            )
                            ->when(
                                $data['received_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('received_date', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('confirmed')
                    ->label('Confirmed Only')
                    ->query(fn (Builder $query): Builder => $query->has('stockMovements')
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('pending')
                    ->label('Pending Confirmation')
                    ->query(fn (Builder $query): Builder => $query->doesntHave('stockMovements')
                    )
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->infolist([
                        Section::make('Inbound Operation Information')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('inbound_number')
                                            ->label('Inbound Number')
                                            ->weight(FontWeight::Bold)
                                            ->copyable(),

                                        TextEntry::make('purchaseOrder.po_number')
                                            ->label('PO Number')
                                            ->weight(FontWeight::SemiBold)
                                            ->url(fn ($record) => route('filament.admin.resources.purchase-orders.edit', ['record' => $record->purchase_order_id]))
                                            ->color('info'),

                                        IconEntry::make('is_confirmed')
                                            ->label('Stock Confirmed')
                                            ->boolean()
                                            ->state(fn ($record) => $record->stockMovements()->exists())
                                            ->trueIcon('heroicon-o-check-circle')
                                            ->falseIcon('heroicon-o-x-circle')
                                            ->trueColor('success')
                                            ->falseColor('danger'),
                                    ]),

                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('purchaseOrder.supplier.name')
                                            ->label('Supplier'),

                                        TextEntry::make('received_date')
                                            ->label('Received Date')
                                            ->dateTime('d M Y, H:i'),

                                        TextEntry::make('receiver.name')
                                            ->label('Received By'),
                                    ]),

                                TextEntry::make('notes')
                                    ->label('Notes')
                                    ->placeholder('No notes')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Received Items')
                            ->schema([
                                RepeatableEntry::make('items')
                                    ->label('')
                                    ->schema([
                                        TextEntry::make('product.name')
                                            ->label('Product')
                                            ->weight(FontWeight::SemiBold),

                                        TextEntry::make('productVariant.name')
                                            ->label('Variant')
                                            ->placeholder('No variant')
                                            ->color('gray'),

                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('received_quantity')
                                                    ->label('Received Quantity')
                                                    ->suffix(' units'),

                                                TextEntry::make('purchaseOrderItem.ordered_quantity')
                                                    ->label('Ordered Quantity')
                                                    ->suffix(' units')
                                                    ->color('gray'),
                                            ]),
                                    ])
                                    ->contained(false),
                            ])
                            ->collapsible(),

                        Section::make('Additional Information')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('created_at')
                                            ->label('Created At')
                                            ->dateTime('d M Y, H:i'),

                                        TextEntry::make('updated_at')
                                            ->label('Last Updated')
                                            ->dateTime('d M Y, H:i'),
                                    ]),
                            ])
                            ->collapsed()
                            ->collapsible(),
                    ])
                    ->modalWidth('5xl'),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Update purchase order status based on received quantities
     */
    protected static function updatePurchaseOrderStatus($purchaseOrder): void
    {
        if (! $purchaseOrder) {
            return;
        }

        $purchaseOrder->load('items', 'inboundOperations.items');

        $totalOrdered = $purchaseOrder->items->sum('ordered_quantity');
        $totalReceived = 0;

        foreach ($purchaseOrder->inboundOperations as $inbound) {
            if ($inbound->stockMovements()->exists()) {
                $totalReceived += $inbound->items->sum('received_quantity');
            }
        }

        if ($totalReceived >= $totalOrdered) {
            $purchaseOrder->update(['status' => PurchaseOrderStatus::COMPLETED]);
        } elseif ($totalReceived > 0) {
            $purchaseOrder->update(['status' => PurchaseOrderStatus::PARTIALLY_RECEIVED]);
        }
    }
}
