<?php

namespace App\Filament\Resources\SalesOrderResource\Schemas;

use App\Enums\SalesOrderStatus;
use App\Models\Product;
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
                                ->options(Product::query()->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('unit_price', $product->selling_price);
                                            
                                            // Get current stock
                                            $stockService = app(StockMovementService::class);
                                            $currentStock = $stockService->getCurrentStock($product);
                                            $set('available_stock', $currentStock);
                                        }
                                    }
                                })
                                ->disableOptionWhen(function ($value, $state, callable $get) {
                                    // Disable already selected products
                                    $items = $get('../../items') ?? [];
                                    return collect($items)
                                        ->pluck('product_id')
                                        ->filter()
                                        ->contains($value);
                                })
                                ->columnSpan(2),
                            
                            Forms\Components\Placeholder::make('available_stock')
                                ->label('Available Stock')
                                ->content(function (callable $get): string {
                                    $productId = $get('product_id');
                                    if (!$productId) {
                                        return '-';
                                    }
                                    
                                    $product = Product::find($productId);
                                    if (!$product) {
                                        return '-';
                                    }
                                    
                                    $stockService = app(StockMovementService::class);
                                    $currentStock = $stockService->getCurrentStock($product);
                                    $quantity = $get('quantity') ?? 0;
                                    
                                    $color = $currentStock >= $quantity ? 'success' : 'danger';
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
                                    return 'Rp ' . number_format($subtotal, 0, ',', '.');
                                })
                                ->columnSpan(1),
                        ])
                        ->columns(6)
                        ->defaultItems(1)
                        ->addActionLabel('Add Product')
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => 
                            isset($state['product_id']) 
                                ? Product::find($state['product_id'])?->name 
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
                            return 'Rp ' . number_format($total, 0, ',', '.');
                        })
                        ->columnSpanFull(),
                ])
                ->collapsible(),
        ]);
    }
}
