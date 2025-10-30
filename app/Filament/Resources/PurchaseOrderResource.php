<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Filament\Resources\PurchaseOrderResource\Schemas\PurchaseOrderForm;
use App\Filament\Resources\PurchaseOrderResource\Schemas\PurchaseOrderTable;
use App\Models\PurchaseOrder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static string | \UnitEnum | null $navigationGroup = 'Transactions';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Purchase Orders';

    protected static ?string $modelLabel = 'Purchase Order';

    protected static ?string $pluralModelLabel = 'Purchase Orders';

    public static function form(Schema $schema): Schema
    {
        return PurchaseOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchaseOrderTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
