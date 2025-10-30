<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockTable extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Low Stock Products')
            ->query(
                Product::query()
                    ->select('products.*')
                    ->selectRaw('COALESCE((SELECT SUM(quantity) FROM stock_movements WHERE stock_movements.product_id = products.id), 0) as current_stock')
                    ->havingRaw('current_stock < products.minimum_stock')
                    ->orderBy('current_stock', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Current Stock')
                    ->badge()
                    ->color('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('minimum_stock')
                    ->label('Minimum Stock')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rack_location')
                    ->label('Location')
                    ->sortable(),
            ])
            ->paginated([5, 10]);
    }
}
