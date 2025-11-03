<?php

namespace App\Filament\Resources\OutboundOperationResource\Schemas;

use App\Models\OutboundOperation;
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

class OutboundOperationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['salesOrder.customer', 'preparer', 'stockMovements']))
            ->columns([
                Tables\Columns\TextColumn::make('outbound_number')
                    ->label('Outbound Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('salesOrder.so_number')
                    ->label('SO Number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('salesOrder.customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('shipped_date')
                    ->label('Shipped Date')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('preparer.name')
                    ->label('Prepared By')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('stock_recorded')
                    ->label('Stock Recorded')
                    ->boolean()
                    ->getStateUsing(function (OutboundOperation $record): bool {
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
                Tables\Filters\SelectFilter::make('sales_order')
                    ->relationship('salesOrder', 'so_number')
                    ->label('Sales Order')
                    ->preload()
                    ->multiple(),

                Tables\Filters\Filter::make('shipped_date')
                    ->form([
                        Forms\Components\DatePicker::make('shipped_from')
                            ->label('Shipped Date From'),
                        Forms\Components\DatePicker::make('shipped_until')
                            ->label('Shipped Date Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['shipped_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('shipped_date', '>=', $date),
                            )
                            ->when(
                                $data['shipped_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('shipped_date', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('stock_recorded')
                    ->label('Stock Recorded')
                    ->query(fn (Builder $query): Builder => $query->has('stockMovements')),

                Tables\Filters\Filter::make('stock_not_recorded')
                    ->label('Stock Not Recorded')
                    ->query(fn (Builder $query): Builder => $query->doesntHave('stockMovements')),
            ])
            ->recordActions([
                ViewAction::make()
                    ->infolist([
                        Section::make('Outbound Operation Information')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('outbound_number')
                                            ->label('Outbound Number')
                                            ->weight(FontWeight::Bold)
                                            ->copyable(),

                                        TextEntry::make('salesOrder.so_number')
                                            ->label('SO Number')
                                            ->weight(FontWeight::SemiBold)
                                            ->url(fn ($record) => route('filament.admin.resources.sales-orders.edit', ['record' => $record->sales_order_id]))
                                            ->color('info'),

                                        IconEntry::make('stock_recorded')
                                            ->label('Stock Recorded')
                                            ->boolean()
                                            ->state(fn ($record) => $record->stockMovements()->exists())
                                            ->trueIcon('heroicon-o-check-circle')
                                            ->falseIcon('heroicon-o-x-circle')
                                            ->trueColor('success')
                                            ->falseColor('danger'),
                                    ]),

                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('salesOrder.customer.name')
                                            ->label('Customer'),

                                        TextEntry::make('shipped_date')
                                            ->label('Shipped Date')
                                            ->dateTime('d M Y, H:i'),

                                        TextEntry::make('preparer.name')
                                            ->label('Prepared By'),
                                    ]),

                                TextEntry::make('notes')
                                    ->label('Notes')
                                    ->placeholder('No notes')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Shipped Items')
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
                                                TextEntry::make('shipped_quantity')
                                                    ->label('Shipped Quantity')
                                                    ->suffix(' units'),

                                                TextEntry::make('salesOrderItem.quantity')
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
}
