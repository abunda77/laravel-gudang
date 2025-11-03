<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesOrderResource\Pages;
use App\Filament\Resources\SalesOrderResource\Schemas\SalesOrderForm;
use App\Filament\Resources\SalesOrderResource\Schemas\SalesOrderTable;
use App\Models\SalesOrder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class SalesOrderResource extends Resource
{
    protected static ?string $model = SalesOrder::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string | \UnitEnum | null $navigationGroup = 'Transactions';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Sales Orders';

    protected static ?string $modelLabel = 'Sales Order';

    protected static ?string $pluralModelLabel = 'Sales Orders';

    public static function getNavigationBadge(): ?string
    {
        // Show count of draft sales orders pending approval
        $pendingCount = SalesOrder::where('status', \App\Enums\SalesOrderStatus::DRAFT)->count();

        return $pendingCount > 0 ? (string) $pendingCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Draft orders pending approval';
    }

    public static function form(Schema $schema): Schema
    {
        return SalesOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalesOrderTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesOrders::route('/'),
            'create' => Pages\CreateSalesOrder::route('/create'),
            'edit' => Pages\EditSalesOrder::route('/{record}/edit'),
        ];
    }
}
