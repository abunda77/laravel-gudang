<?php

namespace App\Filament\Resources\SalesOrderResource\Schemas;

use App\Enums\SalesOrderStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\StockMovementService;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SalesOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Sales Order Information')
                ->schema([
                    Forms\Components\TextInput::make('so_number')
                        ->label('SO Number')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('Auto-generated')
                        ->helperText('Sales order number will be generated automatically')
                        ->columnSpan(1),

                    Forms\Components\Select::make('customer_id')
                        ->label('Customer')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('company')
                                ->maxLength(255),
                            Forms\Components\Textarea::make('address')
                                ->rows(2),
                            Forms\Components\TextInput::make('email')
                                ->email()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('phone')
                                ->tel()
                                ->maxLength(50),
                            Forms\Components\Select::make('type')
                                ->options([
                                    'wholesale' => 'Wholesale',
                                    'retail' => 'Retail',
                                ])
                                ->required(),
                        ])
                        ->columnSpan(1),

                    Forms\Components\DatePicker::make('order_date')
                        ->label('Order Date')
                        ->required()
                        ->default(now())
                        ->native(false)
                        ->columnSpan(1),

                    Forms\Components\Select::make('sales_user_id')
                        ->label('Sales Person')
                        ->relationship('salesUser', 'name')
                        ->searchable()
                        ->preload()
                        ->default(auth()->id())
                        ->columnSpan(1),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            SalesOrderStatus::DRAFT->value => 'Draft',
                            SalesOrderStatus::APPROVED->value => 'Approved',
                            SalesOrderStatus::PARTIALLY_FULFILLED->value => 'Partially Fulfilled',
                            SalesOrderStatus::COMPLETED->value => 'Completed',
                            SalesOrderStatus::CANCELLED->value => 'Cancelled',
                        ])
                        ->default(SalesOrderStatus::DRAFT->value)
                        ->required()
                        ->disabled(fn ($record) => $record && $record->status !== SalesOrderStatus::DRAFT)
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
                                            $set('unit_price', $product->selling_price);

                                            // If product has only one variant, auto-select it
                                            if ($product->variants->count() === 1) {
                                                $variant = $product->variants->first();
                                                $set('product_variant_id', $variant->id);
                                                $set('unit_price', $variant->selling_price ?? $product->selling_price);
                                                
                                                // Get variant stock
                                                $stockService = app(StockMovementService::class);
                                                $currentStock = $stockService->getCurrentStockForVariant($variant);
                                                $set('available_stock', $currentStock);
                                            } elseif ($product->variants->isEmpty()) {
                                                // Get product stock (no variants)
                                                $stockService = app(StockMovementService::class);
                                                $currentStock = $stockService->getCurrentStock($product);
                                                $set('available_stock', $currentStock);
                                            }
                                        }
                                    }
                                })
                                ->columnSpan(1),

                            Forms\Components\Select::make('product_variant_id')
                                ->label('Variant')
                                ->options(function (callable $get) {
                                    $productId = $get('product_id');
                                    if (! $productId) {
                                        return [];
                                    }

                                    $product = Product::with('variants')->find($productId);
                                    if (! $product || $product->variants->isEmpty()) {
                                        return [];
                                    }

                                    return $product->variants->pluck('name', 'id');
                                })
                                ->searchable()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if ($state) {
                                        $variant = ProductVariant::with('product')->find($state);
                                        if ($variant) {
                                            $set('unit_price', $variant->selling_price ?? $variant->product->selling_price);
                                            
                                            // Get variant stock
                                            $stockService = app(StockMovementService::class);
                                            $currentStock = $stockService->getCurrentStockForVariant($variant);
                                            $set('available_stock', $currentStock);
                                        }
                                    }
                                })
                                ->visible(function (callable $get) {
                                    $productId = $get('product_id');
                                    if (! $productId) {
                                        return false;
                                    }

                                    $product = Product::with('variants')->find($productId);

                                    return $product && $product->variants->isNotEmpty();
                                })
                                ->required(function (callable $get) {
                                    $productId = $get('product_id');
                                    if (! $productId) {
                                        return false;
                                    }

                                    $product = Product::with('variants')->find($productId);

                                    return $product && $product->variants->isNotEmpty();
                                })
                                ->helperText('Select product variant')
                                ->columnSpan(1),

                            Forms\Components\Placeholder::make('available_stock')
                                ->label('Available Stock')
                                ->content(function (callable $get): string {
                                    $productId = $get('product_id');
                                    $variantId = $get('product_variant_id');
                                    
                                    if (! $productId) {
                                        return '-';
                                    }

                                    $stockService = app(StockMovementService::class);
                                    $quantity = $get('quantity') ?? 0;
                                    
                                    // Check if product has variants
                                    $product = Product::with('variants')->find($productId);
                                    if (! $product) {
                                        return '-';
                                    }
                                    
                                    // If product has variants but none selected yet
                                    if ($product->variants->isNotEmpty() && ! $variantId) {
                                        return '- (Select variant first)';
                                    }
                                    
                                    // Get stock for variant or product
                                    if ($variantId) {
                                        $variant = ProductVariant::find($variantId);
                                        if (! $variant) {
                                            return '-';
                                        }
                                        $currentStock = $stockService->getCurrentStockForVariant($variant);
                                    } else {
                                        $currentStock = $stockService->getCurrentStock($product);
                                    }

                                    $status = $currentStock >= $quantity ? '✓' : '✗';

                                    return "{$status} {$currentStock} units";
                                })
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('quantity')
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
                                    $quantity = $get('quantity') ?? 0;
                                    $set('subtotal', $quantity * $state);
                                })
                                ->columnSpan(1),

                            Forms\Components\Placeholder::make('subtotal')
                                ->label('Subtotal')
                                ->content(function (callable $get): string {
                                    $quantity = $get('quantity') ?? 0;
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
                        ->itemLabel(fn (array $state): ?string => isset($state['product_id'])
                                ? (function () use ($state) {
                                    $product = Product::find($state['product_id']);
                                    if (! $product) {
                                        return null;
                                    }

                                    if (isset($state['product_variant_id']) && $state['product_variant_id']) {
                                        $variant = ProductVariant::find($state['product_variant_id']);

                                        return $variant ? $product->name.' - '.$variant->name : $product->name;
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
                                return ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                            });

                            return 'Rp '.number_format($total, 0, ',', '.');
                        })
                        ->columnSpanFull(),
                ])
                ->collapsible(),
        ]);
    }
}
