<?php

namespace App\Filament\Resources\OutboundOperationResource\Schemas;

use App\Enums\SalesOrderStatus;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Services\StockMovementService;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OutboundOperationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Outbound Operation Information')
                ->schema([
                    Forms\Components\TextInput::make('outbound_number')
                        ->label('Outbound Number')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('Auto-generated')
                        ->helperText('Outbound operation number will be generated automatically')
                        ->columnSpan(1),
                    
                    Forms\Components\Select::make('sales_order_id')
                        ->label('Sales Order')
                        ->options(function () {
                            return SalesOrder::where('status', SalesOrderStatus::APPROVED)
                                ->orWhere('status', SalesOrderStatus::PARTIALLY_FULFILLED)
                                ->with('customer')
                                ->get()
                                ->mapWithKeys(function ($so) {
                                    return [$so->id => "{$so->so_number} - {$so->customer->name}"];
                                });
                        })
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            if ($state) {
                                $salesOrder = SalesOrder::with('items.product')->find($state);
                                if ($salesOrder) {
                                    // Auto-load items from sales order
                                    $items = $salesOrder->items->map(function ($item) {
                                        $stockService = app(StockMovementService::class);
                                        $currentStock = $stockService->getCurrentStock($item->product);
                                        
                                        return [
                                            'product_id' => $item->product_id,
                                            'ordered_quantity' => $item->quantity,
                                            'shipped_quantity' => $item->quantity,
                                            'available_stock' => $currentStock,
                                            'confirmed' => true,
                                        ];
                                    })->toArray();
                                    
                                    $set('items', $items);
                                }
                            }
                        })
                        ->disableOptionWhen(function ($value) {
                            // Disable sales orders that are already fully fulfilled
                            $salesOrder = SalesOrder::find($value);
                            return $salesOrder && $salesOrder->status === SalesOrderStatus::COMPLETED;
                        })
                        ->columnSpan(1),
                    
                    Forms\Components\DateTimePicker::make('shipped_date')
                        ->label('Shipped Date')
                        ->required()
                        ->default(now())
                        ->native(false)
                        ->columnSpan(1),
                    
                    Forms\Components\Select::make('prepared_by')
                        ->label('Prepared By')
                        ->relationship('preparer', 'name')
                        ->searchable()
                        ->preload()
                        ->default(auth()->id())
                        ->required()
                        ->columnSpan(1),
                    
                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Outbound Items')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->label('Product')
                                ->options(Product::query()->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(2),
                            
                            Forms\Components\TextInput::make('ordered_quantity')
                                ->label('Ordered Qty')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(false)
                                ->columnSpan(1),
                            
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
                                    $shippedQty = $get('shipped_quantity') ?? 0;
                                    
                                    $status = $currentStock >= $shippedQty ? '✓' : '✗';
                                    
                                    return "{$status} {$currentStock} units";
                                })
                                ->columnSpan(1),
                            
                            Forms\Components\TextInput::make('shipped_quantity')
                                ->label('Shipped Qty')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->reactive()
                                ->columnSpan(1),
                            
                            Forms\Components\Checkbox::make('confirmed')
                                ->label('Confirmed')
                                ->default(true)
                                ->columnSpan(1),
                        ])
                        ->columns(6)
                        ->defaultItems(0)
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => 
                            isset($state['product_id']) 
                                ? Product::find($state['product_id'])?->name 
                                : null
                        )
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->description('Items are automatically loaded from the selected sales order'),
        ]);
    }
}
