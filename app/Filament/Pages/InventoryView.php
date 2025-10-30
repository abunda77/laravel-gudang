<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Services\StockMovementService;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryView extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cube-transparent';

    protected string $view = 'filament.pages.inventory-view';

    protected static string | \UnitEnum | null $navigationGroup = 'Stock Management';

    protected static ?string $navigationLabel = 'Inventory';

    protected static ?string $title = 'Real-Time Inventory';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        // Show count of low stock products
        $lowStockCount = Product::query()
            ->whereRaw('(
                SELECT COALESCE(SUM(quantity), 0)
                FROM stock_movements
                WHERE stock_movements.product_id = products.id
            ) < products.minimum_stock')
            ->count();

        return $lowStockCount > 0 ? (string) $lowStockCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Products below minimum stock';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query()->with(['category', 'stockMovements']))
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
                    ->wrap()
                    ->description(fn (Product $record): ?string => $record->description),
                
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Current Stock')
                    ->getStateUsing(function (Product $record): int {
                        $stockService = app(StockMovementService::class);
                        return $stockService->getCurrentStock($record);
                    })
                    ->badge()
                    ->color(function (int $state, Product $record): string {
                        if ($state < $record->minimum_stock) {
                            return 'danger';
                        } elseif ($state < ($record->minimum_stock * 1.5)) {
                            return 'warning';
                        }
                        return 'success';
                    })
                    ->suffix(fn (Product $record): string => ' ' . $record->unit)
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->leftJoin('stock_movements', 'products.id', '=', 'stock_movements.product_id')
                            ->groupBy('products.id')
                            ->orderByRaw("SUM(COALESCE(stock_movements.quantity, 0)) {$direction}");
                    }),
                
                Tables\Columns\TextColumn::make('minimum_stock')
                    ->label('Min. Stock')
                    ->badge()
                    ->color('gray')
                    ->suffix(fn (Product $record): string => ' ' . $record->unit),
                
                Tables\Columns\TextColumn::make('rack_location')
                    ->label('Location')
                    ->placeholder('Not set')
                    ->icon('heroicon-o-map-pin')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('stock_value')
                    ->label('Stock Value')
                    ->getStateUsing(function (Product $record): float {
                        $stockService = app(StockMovementService::class);
                        $currentStock = $stockService->getCurrentStock($record);
                        return $currentStock * $record->purchase_price;
                    })
                    ->money('IDR')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->label('Product Category')
                    ->preload()
                    ->multiple()
                    ->searchable(),
                
                Tables\Filters\Filter::make('low_stock')
                    ->label('Low Stock (Below Minimum)')
                    ->query(fn (Builder $query): Builder =>
                        $query->whereRaw('(
                            SELECT COALESCE(SUM(quantity), 0)
                            FROM stock_movements
                            WHERE stock_movements.product_id = products.id
                        ) < products.minimum_stock')
                    )
                    ->toggle(),
                
                Tables\Filters\Filter::make('out_of_stock')
                    ->label('Out of Stock')
                    ->query(fn (Builder $query): Builder =>
                        $query->whereRaw('(
                            SELECT COALESCE(SUM(quantity), 0)
                            FROM stock_movements
                            WHERE stock_movements.product_id = products.id
                        ) <= 0')
                    )
                    ->toggle(),
            ])
            // ->actions([
            //     Tables\Actions\Action::make('view_stock_card')
            //         ->label('Stock Card')
            //         ->icon('heroicon-o-document-text')
            //         ->url(fn (Product $record): string => 
            //             route('filament.admin.pages.stock-card-report', ['product' => $record->id])
            //         )
            //         ->openUrlInNewTab(),
            // ])
            ->defaultSort('name', 'asc')
            ->poll('30s')
            ->striped();
    }
}
