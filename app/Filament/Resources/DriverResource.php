<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverResource\Pages;
use App\Filament\Resources\DriverResource\Schemas\DriverForm;
use App\Filament\Resources\DriverResource\Schemas\DriverTable;
use App\Models\Driver;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user';

    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return DriverForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DriverTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDrivers::route('/'),
            'create' => Pages\CreateDriver::route('/create'),
            'edit' => Pages\EditDriver::route('/{record}/edit'),
        ];
    }
}
