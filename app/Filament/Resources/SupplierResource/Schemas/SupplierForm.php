<?php

namespace App\Filament\Resources\SupplierResource\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Supplier Information')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Supplier Name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('contact')
                        ->label('Contact Person')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('bank_account')
                        ->label('Bank Account Number')
                        ->maxLength(255)
                        ->columnSpan(2),

                    Forms\Components\Textarea::make('address')
                        ->label('Address')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('supplied_products')
                        ->label('Supplied Products')
                        ->helperText('List the main products or categories this supplier provides')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }
}
