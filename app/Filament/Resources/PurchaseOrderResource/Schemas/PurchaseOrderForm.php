<?php

namespace App\Filament\Resources\PurchaseOrderResource\Schemas;

use App\Enums\PurchaseOrderStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Purchase Order Information')
                ->schema([
                    Forms\Components\TextInput::make('po_number')
                        ->label('PO Number')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('Auto-generated')
                        ->helperText('Purchase order number will be generated automatically')
                        ->columnSpan(1),

                    Forms\Components\Select::make('supplier_id')
                        ->label('Supplier')
                        ->relationship('supplier', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('contact')
                                ->maxLength(255),
                            Forms\Components\Textarea::make('address')
                                ->rows(2),
                            Forms\Components\TextInput::make('bank_account')
                                ->maxLength(255),
                        ])
                        ->columnSpan(1),

                    Forms\Components\DatePicker::make('order_date')
                        ->label('Order Date')
                        ->required()
                        ->default(now())
                        ->native(false)
                        ->columnSpan(1),

                    Forms\Components\DatePicker::make('expected_date')
                        ->label('Expected Delivery Date')
                        ->native(false)
                        ->after('order_date')
                        ->columnSpan(1),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            PurchaseOrderStatus::DRAFT->value => 'Draft',
                            PurchaseOrderStatus::SENT->value => 'Sent',
                            PurchaseOrderStatus::PARTIALLY_RECEIVED->value => 'Partially Received',
                            PurchaseOrderStatus::COMPLETED->value => 'Completed',
                            PurchaseOrderStatus::CANCELLED->value => 'Cancelled',
                        ])
                        ->default(PurchaseOrderStatus::DRAFT->value)
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Order Items')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->label('Product')
                                ->options(fn () => Product::query()->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    // Reset variant when product changes
                                    $set('product_variant_id', null);
                                    
                                    if ($state) {
                                        $product = Product::with('variants')->find($state);
                                        if ($product) {
                                            $set('unit_price', $product->purchase_price);
                                            
                                            // If product has only one variant, auto-select it
                                            if ($product->variants->count() === 1) {
                                                $set('product_variant_id', $product->variants->first()->id);
                                            }
                                        }
                                    }
                                })
                                ->columnSpan(1),
                            
                            Forms\Components\Select::make('product_variant_id')
                                ->label('Variant')
                                ->options(function (callable $get) {
                                    $productId = $get('product_id');
                                    if (!$productId) {
                                        return [];
                                    }
                                    
                                    $product = Product::with('variants')->find($productId);
                                    if (!$product || $product->variants->isEmpty()) {
                                        return [];
                                    }
                                    
                                    return $product->variants->pluck('name', 'id');
                                })
                                ->searchable()
                                ->reactive()
                                ->visible(function (callable $get) {
                                    $productId = $get('product_id');
                                    if (!$productId) {
                                        return false;
                                    }
                                    
                                    $product = Product::with('variants')->find($productId);
                                    return $product && $product->variants->isNotEmpty();
                                })
                                ->required(function (callable $get) {
                                    $productId = $get('product_id');
                                    if (!$productId) {
                                        return false;
                                    }
                                    
                                    $product = Product::with('variants')->find($productId);
                                    return $product && $product->variants->isNotEmpty();
                                })
                                ->helperText('Select product variant')
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('ordered_quantity')
                                ->label('Quantity')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $unitPrice = $get('unit_price') ?? 0;
                                    $set('subtotal', $state * $unitPrice);
                                })
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('unit_price')
                                ->label('Unit Price')
                                ->required()
                                ->numeric()
                                ->prefix('Rp')
                                ->minValue(0)
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $quantity = $get('ordered_quantity') ?? 0;
                                    $set('subtotal', $quantity * $state);
                                })
                                ->columnSpan(1),

                            Forms\Components\Placeholder::make('subtotal')
                                ->label('Subtotal')
                                ->content(function (callable $get): string {
                                    $quantity = $get('ordered_quantity') ?? 0;
                                    $unitPrice = $get('unit_price') ?? 0;
                                    $subtotal = $quantity * $unitPrice;

                                    return 'Rp '.number_format($subtotal, 0, ',', '.');
                                })
                                ->columnSpan(1),
                        ])
                        ->columns(6)
                        ->defaultItems(1)
                        ->addActionLabel('Add Product')
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => 
                            isset($state['product_id']) 
                                ? (function() use ($state) {
                                    $product = Product::find($state['product_id']);
                                    if (!$product) return null;
                                    
                                    if (isset($state['product_variant_id']) && $state['product_variant_id']) {
                                        $variant = ProductVariant::find($state['product_variant_id']);
                                        return $variant ? $product->name . ' - ' . $variant->name : $product->name;
                                    }
                                    
                                    return $product->name;
                                })()
                                : null
                        )
                        ->reorderable(false)
                        ->columnSpanFull(),

                    Forms\Components\Placeholder::make('total_amount_display')
                        ->label('Total Amount')
                        ->content(function (callable $get): string {
                            $items = $get('items') ?? [];
                            $total = collect($items)->sum(function ($item) {
                                return ($item['ordered_quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                            });

                            return 'Rp '.number_format($total, 0, ',', '.');
                        })
                        ->columnSpanFull(),
                ])
                ->collapsible(),
        ]);
    }
}
