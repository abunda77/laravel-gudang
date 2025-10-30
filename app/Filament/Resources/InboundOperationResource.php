<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InboundOperationResource\Pages;
use App\Filament\Resources\InboundOperationResource\Schemas\InboundOperationForm;
use App\Filament\Resources\InboundOperationResource\Schemas\InboundOperationTable;
use App\Models\InboundOperation;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class InboundOperationResource extends Resource
{
    protected static ?string $model = InboundOperation::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static string | \UnitEnum | null $navigationGroup = 'Transactions';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Inbound Operations';

    protected static ?string $modelLabel = 'Inbound Operation';

    protected static ?string $pluralModelLabel = 'Inbound Operations';

    public static function form(Schema $schema): Schema
    {
        return InboundOperationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InboundOperationTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInboundOperations::route('/'),
            'create' => Pages\CreateInboundOperation::route('/create'),
            'edit' => Pages\EditInboundOperation::route('/{record}/edit'),
        ];
    }
}
