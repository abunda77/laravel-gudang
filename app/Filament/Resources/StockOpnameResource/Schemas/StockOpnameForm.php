<?php

namespace App\Filament\Resources\StockOpnameResource\Schemas;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\StockMovementService;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockOpnameForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Stock Opname Information')
                ->schema([
                    Forms\Components\TextInput::make('opname_number')
                        ->label('Opname Number')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('Auto-generated')
                        ->helperText('Opname number will be generated automatically')
                        ->columnSpan(1),
                    
                    Forms\Components\DatePicker::make('opname_date')
                        ->label('Opname Date')
                        ->required()
                        ->default(now())
                        ->native(false)
                        ->maxDate(now())
                        ->columnSpan(1),
                    
                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->placeholder('Enter any notes about this stock opname...')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Product Stock Count')
                ->description('Enter the physical stock count for each product. The system will automatically calculate variances.')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->label('Product')
                                ->options(Product::query()->orderBy('name')->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if ($state) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            // Reset variant selection when product changes
                                            $set('product_variant_id', null);
                                            
                                            // If product has no variants, calculate stock directly
                                            if (!$product->variants()->exists()) {
                                                $stockService = app(StockMovementService::class);
                                                $currentStock = $stockService->getCurrentStock($product);
                                                $set('system_stock', $currentStock);
                                                
                                                if (!$get('physical_stock')) {
                                                    $set('physical_stock', $currentStock);
                                                }
                                            } else {
                                                // If product has variants, wait for variant selection
                                                $set('system_stock', 0);
                                                $set('physical_stock', 0);
                                            }
                                        }
                                    }
                                })
                                ->disabled(fn ($context) => $context === 'edit')
                                ->dehydrated()
                                ->columnSpan(2),
                            
                            Forms\Components\Select::make('product_variant_id')
                                ->label('Variant')
                                ->options(function (callable $get) {
                                    $productId = $get('product_id');
                                    if (!$productId) {
                                        return [];
                                    }
                                    return ProductVariant::where('product_id', $productId)
                                        ->orderBy('name')
                                        ->pluck('name', 'id');
                                })
                                ->searchable()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if ($state) {
                                        $variant = ProductVariant::find($state);
                                        if ($variant) {
                                            $stockService = app(StockMovementService::class);
                                            $currentStock = $stockService->getCurrentStockForVariant($variant);
                                            $set('system_stock', $currentStock);
                                            
                                            if (!$get('physical_stock')) {
                                                $set('physical_stock', $currentStock);
                                            }
                                        }
                                    }
                                })
                                ->visible(function (callable $get) {
                                    $productId = $get('product_id');
                                    if (!$productId) {
                                        return false;
                                    }
                                    $product = Product::find($productId);
                                    return $product && $product->variants()->exists();
                                })
                                ->required(function (callable $get) {
                                    $productId = $get('product_id');
                                    if (!$productId) {
                                        return false;
                                    }
                                    $product = Product::find($productId);
                                    return $product && $product->variants()->exists();
                                })
                                ->disabled(fn ($context) => $context === 'edit')
                                ->dehydrated()
                                ->columnSpan(2),
                            
                            Forms\Components\TextInput::make('system_stock')
                                ->label('System Stock')
                                ->numeric()
                                ->disabled()
                                ->dehydrated()
                                ->suffix(function (callable $get) {
                                    $productId = $get('product_id');
                                    if ($productId) {
                                        $product = Product::find($productId);
                                        return $product ? $product->unit : '';
                                    }
                                    return '';
                                })
                                ->helperText('Current stock in system')
                                ->columnSpan(1),
                            
                            Forms\Components\TextInput::make('physical_stock')
                                ->label('Physical Stock')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $systemStock = $get('system_stock') ?? 0;
                                    $physicalStock = $state ?? 0;
                                    $variance = $physicalStock - $systemStock;
                                    $set('variance', $variance);
                                })
                                ->suffix(function (callable $get) {
                                    $productId = $get('product_id');
                                    if ($productId) {
                                        $product = Product::find($productId);
                                        return $product ? $product->unit : '';
                                    }
                                    return '';
                                })
                                ->helperText('Enter the actual counted stock')
                                ->columnSpan(1),
                            
                            Forms\Components\Placeholder::make('variance_display')
                                ->label('Variance')
                                ->content(function (callable $get): string {
                                    $systemStock = $get('system_stock') ?? 0;
                                    $physicalStock = $get('physical_stock') ?? 0;
                                    $variance = $physicalStock - $systemStock;
                                    
                                    if ($variance > 0) {
                                        return '+' . $variance . ' (Surplus)';
                                    } elseif ($variance < 0) {
                                        return $variance . ' (Shortage)';
                                    } else {
                                        return '0 (Match)';
                                    }
                                })
                                ->columnSpan(1),
                            
                            Forms\Components\Hidden::make('variance')
                                ->dehydrated(),
                        ])
                        ->columns(5)
                        ->defaultItems(1)
                        ->addActionLabel('Add Product')
                        ->reorderable(false)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => 
                            isset($state['product_id']) 
                                ? (Product::find($state['product_id'])?->name . 
                                   (isset($state['product_variant_id']) && $state['product_variant_id'] 
                                       ? ' - ' . ProductVariant::find($state['product_variant_id'])?->name 
                                       : ''))
                                : 'New Product'
                        )
                        ->columnSpanFull()
                        ->live(),
                ])
                ->collapsible(),
        ]);
    }
}
