<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutboundOperationResource\Pages;
use App\Filament\Resources\OutboundOperationResource\Schemas\OutboundOperationForm;
use App\Filament\Resources\OutboundOperationResource\Schemas\OutboundOperationTable;
use App\Models\OutboundOperation;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class OutboundOperationResource extends Resource
{
    protected static ?string $model = OutboundOperation::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string | \UnitEnum | null $navigationGroup = 'Transactions';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Outbound Operations';

    protected static ?string $modelLabel = 'Outbound Operation';

    protected static ?string $pluralModelLabel = 'Outbound Operations';

    public static function form(Schema $schema): Schema
    {
        return OutboundOperationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OutboundOperationTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOutboundOperations::route('/'),
            'create' => Pages\CreateOutboundOperation::route('/create'),
            'edit' => Pages\EditOutboundOperation::route('/{record}/edit'),
        ];
    }
}
