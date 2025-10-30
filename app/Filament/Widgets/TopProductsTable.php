<?php

namespace App\Filament\Widgets;

use App\Models\OutboundOperationItem;
use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TopProductsTable extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Top 5 Best-Selling Products')
            ->query(
                Product::query()
                    ->select('products.*')
                    ->selectRaw('COALESCE(SUM(outbound_operation_items.shipped_quantity), 0) as total_shipped')
                    ->leftJoin('outbound_operation_items', 'products.id', '=', 'outbound_operation_items.product_id')
                    ->groupBy('products.id')
                    ->orderByDesc('total_shipped')
                    ->limit(5)
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
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_shipped')
                    ->label('Total Sold')
                    ->badge()
                    ->color('success')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
