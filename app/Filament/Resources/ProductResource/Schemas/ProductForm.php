<?php

namespace App\Filament\Resources\ProductResource\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Basic Information')
                ->schema([
                    Forms\Components\TextInput::make('sku')
                        ->label('SKU')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(100)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('name')
                        ->label('Product Name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\Textarea::make('description')
                                ->rows(2),
                        ])
                        ->columnSpan(1),
                ])
                ->columns(2),

            Section::make('Pricing & Stock')
                ->schema([
                    Forms\Components\TextInput::make('unit')
                        ->label('Unit of Measure')
                        ->required()
                        ->maxLength(50)
                        ->placeholder('e.g., pcs, kg, box')
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('purchase_price')
                        ->label('Purchase Price')
                        ->required()
                        ->numeric()
                        ->prefix('Rp')
                        ->minValue(0)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('selling_price')
                        ->label('Selling Price')
                        ->required()
                        ->numeric()
                        ->prefix('Rp')
                        ->minValue(0)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('minimum_stock')
                        ->label('Minimum Stock Threshold')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText('Alert will be shown when stock falls below this level')
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('rack_location')
                        ->label('Rack Location')
                        ->maxLength(100)
                        ->placeholder('e.g., A-01-02')
                        ->columnSpan(2),
                ])
                ->columns(2),

            Section::make('Product Variants')
                ->schema([
                    Forms\Components\Repeater::make('variants')
                        ->relationship()
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Variant Name')
                                ->required()
                                ->placeholder('e.g., Red - Large')
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('sku')
                                ->label('Variant SKU')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->placeholder('e.g., PROD-001-RED-L')
                                ->columnSpan(1),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->addActionLabel('Add Variant')
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                ])
                ->collapsible()
                ->collapsed(),
        ]);
    }
}
