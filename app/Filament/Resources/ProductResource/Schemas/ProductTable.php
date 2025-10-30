<?php

namespace App\Filament\Resources\ProductResource\Schemas;

use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['category']))
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Current Stock')
                    ->getStateUsing(fn (Product $record): int => $record->getCurrentStock())
                    ->badge()
                    ->color(fn (int $state, Product $record): string => $state < $record->minimum_stock ? 'danger' : 'success'
                    )
                    ->suffix(fn (Product $record): string => ' '.$record->unit)
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->leftJoin('stock_movements', 'products.id', '=', 'stock_movements.product_id')
                            ->groupBy('products.id')
                            ->orderByRaw("SUM(COALESCE(stock_movements.quantity, 0)) {$direction}");
                    }),

                Tables\Columns\TextColumn::make('selling_price')
                    ->label('Selling Price')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('rack_location')
                    ->label('Location')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->label('Category')
                    ->preload()
                    ->multiple(),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Low Stock')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('(
                            SELECT COALESCE(SUM(quantity), 0)
                            FROM stock_movements
                            WHERE stock_movements.product_id = products.id
                        ) < products.minimum_stock')
                    )
                    ->toggle(),
            ])
            ->recordActions([
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
