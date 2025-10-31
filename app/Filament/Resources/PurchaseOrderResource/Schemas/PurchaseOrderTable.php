<?php

namespace App\Filament\Resources\PurchaseOrderResource\Schemas;

use App\Enums\PurchaseOrderStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms as Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PurchaseOrderTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['supplier', 'creator']))
            ->columns([
                Tables\Columns\TextColumn::make('po_number')
                    ->label('PO Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('order_date')
                    ->label('Order Date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('expected_date')
                    ->label('Expected Date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (PurchaseOrderStatus $state): string => match ($state) {
                        PurchaseOrderStatus::DRAFT => 'gray',
                        PurchaseOrderStatus::SENT => 'info',
                        PurchaseOrderStatus::PARTIALLY_RECEIVED => 'warning',
                        PurchaseOrderStatus::COMPLETED => 'success',
                        PurchaseOrderStatus::CANCELLED => 'danger',
                    })
                    ->formatStateUsing(fn (PurchaseOrderStatus $state): string => match ($state) {
                        PurchaseOrderStatus::DRAFT => 'Draft',
                        PurchaseOrderStatus::SENT => 'Sent',
                        PurchaseOrderStatus::PARTIALLY_RECEIVED => 'Partially Received',
                        PurchaseOrderStatus::COMPLETED => 'Completed',
                        PurchaseOrderStatus::CANCELLED => 'Cancelled',
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('IDR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR')
                            ->label('Total'),
                    ]),
                
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        PurchaseOrderStatus::DRAFT->value => 'Draft',
                        PurchaseOrderStatus::SENT->value => 'Sent',
                        PurchaseOrderStatus::PARTIALLY_RECEIVED->value => 'Partially Received',
                        PurchaseOrderStatus::COMPLETED->value => 'Completed',
                        PurchaseOrderStatus::CANCELLED->value => 'Cancelled',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('supplier')
                    ->relationship('supplier', 'name')
                    ->label('Supplier')
                    ->preload()
                    ->multiple(),
                
                Tables\Filters\Filter::make('order_date')
                    ->form([
                        Forms\Components\DatePicker::make('order_from')
                            ->label('Order Date From'),
                        Forms\Components\DatePicker::make('order_until')
                            ->label('Order Date Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['order_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('order_date', '>=', $date),
                            )
                            ->when(
                                $data['order_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('order_date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
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
