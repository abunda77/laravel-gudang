<?php

namespace App\Filament\Resources\CustomerResource\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Customer Information')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Customer Name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('company')
                        ->label('Company Name')
                        ->maxLength(255)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('email')
                        ->label('Email Address')
                        ->email()
                        ->maxLength(255)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('phone')
                        ->label('Phone Number')
                        ->tel()
                        ->maxLength(50)
                        ->columnSpan(1),

                    Forms\Components\Select::make('type')
                        ->label('Customer Type')
                        ->options([
                            'wholesale' => 'Wholesale',
                            'retail' => 'Retail',
                        ])
                        ->required()
                        ->default('retail')
                        ->native(false)
                        ->columnSpan(2),

                    Forms\Components\Textarea::make('address')
                        ->label('Address')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }
}
