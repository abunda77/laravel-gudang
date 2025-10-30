<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockOpnameResource\Pages;
use App\Filament\Resources\StockOpnameResource\Schemas\StockOpnameForm;
use App\Filament\Resources\StockOpnameResource\Schemas\StockOpnameTable;
use App\Models\StockOpname;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class StockOpnameResource extends Resource
{
    protected static ?string $model = StockOpname::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string | \UnitEnum | null $navigationGroup = 'Stock Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Stock Opname';

    protected static ?string $modelLabel = 'Stock Opname';

    protected static ?string $pluralModelLabel = 'Stock Opnames';

    public static function getNavigationBadge(): ?string
    {
        // Show count of low stock products as a badge
        $lowStockCount = \App\Models\Product::query()
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

    public static function form(Schema $schema): Schema
    {
        return StockOpnameForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockOpnameTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockOpnames::route('/'),
            'create' => Pages\CreateStockOpname::route('/create'),
            'edit' => Pages\EditStockOpname::route('/{record}/edit'),
            'view' => Pages\ViewStockOpname::route('/{record}'),
        ];
    }
}
