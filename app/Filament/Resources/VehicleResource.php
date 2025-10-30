<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\Schemas\VehicleForm;
use App\Filament\Resources\VehicleResource\Schemas\VehicleTable;
use App\Models\Vehicle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-truck';

    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return VehicleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VehicleTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
