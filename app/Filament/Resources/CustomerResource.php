<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\Schemas\CustomerForm;
use App\Filament\Resources\CustomerResource\Schemas\CustomerTable;
use App\Models\Customer;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerTable::configure($table);
    }

    // public static function infolist(Schema $schema): Schema
    // {
    //     return $schema
    //         ->components([
    //             Section::make('Customer Information')
    //                 ->schema([
    //                     TextEntry::make('name')
    //                         ->label('Name'),
    //                     TextEntry::make('company')
    //                         ->label('Company'),
    //                     TextEntry::make('email')
    //                         ->label('Email')
    //                         ->icon('heroicon-m-envelope'),
    //                     TextEntry::make('phone')
    //                         ->label('Phone')
    //                         ->icon('heroicon-m-phone'),
    //                     TextEntry::make('type')
    //                         ->label('Customer Type')
    //                         ->badge()
    //                         ->color(fn (string $state): string => match ($state) {
    //                             'wholesale' => 'success',
    //                             'retail' => 'info',
    //                             default => 'gray',
    //                         }),
    //                     TextEntry::make('address')
    //                         ->label('Address')
    //                         ->columnSpanFull(),
    //                 ])
    //                 ->columns(2),

    //             Section::make('Transaction History')
    //                 ->schema([
    //                     RepeatableEntry::make('salesOrders')
    //                         ->label('Sales Orders')
    //                         ->schema([
    //                             TextEntry::make('so_number')
    //                                 ->label('SO Number'),
    //                             TextEntry::make('order_date')
    //                                 ->label('Order Date')
    //                                 ->date(),
    //                             TextEntry::make('status')
    //                                 ->badge(),
    //                             TextEntry::make('total_amount')
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
