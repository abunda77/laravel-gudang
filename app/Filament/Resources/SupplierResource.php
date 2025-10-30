<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Filament\Resources\SupplierResource\Schemas\SupplierForm;
use App\Filament\Resources\SupplierResource\Schemas\SupplierTable;
use App\Models\Supplier;
use Filament\Infolists\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return SupplierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupplierTable::configure($table);
    }

    // public static function infolist(Schema $schema): Schema
    // {
    //     return $schema->components([
    //             Components\Section::make('Supplier Information')
    //                 ->schema([
    //                     Components\TextEntry::make('name')
    //                         ->label('Supplier Name'),
    //                     Components\TextEntry::make('contact')
    //                         ->label('Contact Person'),
    //                     Components\TextEntry::make('bank_account')
    //                         ->label('Bank Account')
    //                         ->icon('heroicon-m-credit-card'),
    //                     Components\TextEntry::make('address')
    //                         ->label('Address')
    //                         ->columnSpanFull(),
    //                     Components\TextEntry::make('supplied_products')
    //                         ->label('Supplied Products')
    //                         ->columnSpanFull(),
    //                 ])
    //                 ->columns(2),

    //             Components\Section::make('Purchase History')
    //                 ->schema([
    //                     Components\RepeatableEntry::make('purchaseOrders')
    //                         ->label('Purchase Orders')
    //                         ->schema([
    //                             Components\TextEntry::make('po_number')
    //                                 ->label('PO Number'),
    //                             Components\TextEntry::make('order_date')
    //                                 ->label('Order Date')
    //                                 ->date(),
    //                             Components\TextEntry::make('status')
    //                                 ->badge(),
    //                             Components\TextEntry::make('total_amount')
    //                                 ->label('Total')
    //                                 ->money('IDR'),
    //                         ])
    //                         ->columns(4)
    //                         ->columnSpanFull(),
    //                 ]),
    //         ]);
    // }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'view' => Pages\ViewSupplier::route('/{record}'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
