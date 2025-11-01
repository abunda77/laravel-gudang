<?php

namespace App\Filament\Resources\SalesOrderResource\Schemas;

use App\Enums\SalesOrderStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalesOrderTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['customer', 'salesUser']))
            ->columns([
                Tables\Columns\TextColumn::make('so_number')
                    ->label('SO Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('order_date')
                    ->label('Order Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (SalesOrderStatus $state): string => match ($state) {
                        SalesOrderStatus::DRAFT => 'gray',
                        SalesOrderStatus::APPROVED => 'info',
                        SalesOrderStatus::PARTIALLY_FULFILLED => 'warning',
                        SalesOrderStatus::COMPLETED => 'success',
                        SalesOrderStatus::CANCELLED => 'danger',
                    })
                    ->formatStateUsing(fn (SalesOrderStatus $state): string => match ($state) {
                        SalesOrderStatus::DRAFT => 'Draft',
                        SalesOrderStatus::APPROVED => 'Approved',
                        SalesOrderStatus::PARTIALLY_FULFILLED => 'Partially Fulfilled',
                        SalesOrderStatus::COMPLETED => 'Completed',
                        SalesOrderStatus::CANCELLED => 'Cancelled',
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

                Tables\Columns\TextColumn::make('salesUser.name')
                    ->label('Sales Person')
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
                        SalesOrderStatus::DRAFT->value => 'Draft',
                        SalesOrderStatus::APPROVED->value => 'Approved',
                        SalesOrderStatus::PARTIALLY_FULFILLED->value => 'Partially Fulfilled',
                        SalesOrderStatus::COMPLETED->value => 'Completed',
                        SalesOrderStatus::CANCELLED->value => 'Cancelled',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('customer')
                    ->relationship('customer', 'name')
                    ->label('Customer')
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
