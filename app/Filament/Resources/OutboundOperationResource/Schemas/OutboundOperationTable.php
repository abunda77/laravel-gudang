<?php

namespace App\Filament\Resources\OutboundOperationResource\Schemas;

use App\Enums\SalesOrderStatus;
use App\Models\OutboundOperation;
use App\Services\StockMovementService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
            // ->actions([
            //     Tables\Actions\EditAction::make(),
            //     Tables\Actions\DeleteAction::make(),
            // ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ])
            ->defaultSort('created_at', 'desc');
    }
}
