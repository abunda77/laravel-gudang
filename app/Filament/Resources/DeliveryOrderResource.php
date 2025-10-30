<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryOrderResource\Pages;
use App\Filament\Resources\DeliveryOrderResource\Schemas\DeliveryOrderForm;
use App\Filament\Resources\DeliveryOrderResource\Schemas\DeliveryOrderTable;
use App\Models\DeliveryOrder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class DeliveryOrderResource extends Resource
{
    protected static ?string $model = DeliveryOrder::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-truck';

    protected static string | \UnitEnum | null $navigationGroup = 'Documents';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Delivery Orders';

    protected static ?string $modelLabel = 'Delivery Order';

    protected static ?string $pluralModelLabel = 'Delivery Orders';

    public static function form(Schema $schema): Schema
    {
        return DeliveryOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliveryOrderTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveryOrders::route('/'),
            'create' => Pages\CreateDeliveryOrder::route('/create'),
            'edit' => Pages\EditDeliveryOrder::route('/{record}/edit'),
        ];
    }
}
