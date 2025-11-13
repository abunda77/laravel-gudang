<?php

namespace App\Filament\Resources\InboundOperationResource\Schemas;

use App\Enums\PurchaseOrderStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InboundOperationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Inbound Operation Information')
                ->schema([
                    Forms\Components\TextInput::make('inbound_number')
                        ->label('Inbound Number')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('Auto-generated')
                        ->helperText('Inbound number will be generated automatically')
                        ->columnSpan(1),

                    Forms\Components\Select::make('purchase_order_id')
                        ->label('Purchase Order')
                        ->options(function () {
                            return PurchaseOrder::whereIn('status', [
                                PurchaseOrderStatus::SENT,
                                PurchaseOrderStatus::PARTIALLY_RECEIVED,
                            ])
                                ->with('supplier')
                                ->get()
                                ->mapWithKeys(function ($po) {
                                    return [$po->id => $po->po_number.' - '.$po->supplier->name];
                                });
                        })
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $purchaseOrder = PurchaseOrder::with('items.product.variants', 'items.productVariant')->find($state);
                                if ($purchaseOrder) {
                                    $items = $purchaseOrder->items->map(function ($item) {
                                        return [
                                            'product_id' => $item->product_id,
                                            'product_variant_id' => $item->product_variant_id,
                                            'ordered_quantity' => $item->ordered_quantity,
                                            'received_quantity' => $item->ordered_quantity,
                                        ];
                                    })->toArray();
                                    $set('items', $items);
                                }
                            } else {
                                $set('items', []);
                            }
                        })
                        ->disabled(fn ($context) => $context === 'edit')
                        ->columnSpan(1),

                    Forms\Components\DateTimePicker::make('received_date')
                        ->label('Received Date')
                        ->required()
                        ->default(now())
                        ->native(false)
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(1),

            Section::make('Received Items')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->label('Product')
                                ->options(fn () => Product::query()->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(3),

                            Forms\Components\Select::make('product_variant_id')
                                ->label('Variant')
                                ->options(function (callable $get) {
                                    $productId = $get('product_id');
                                    if (! $productId) {
                                        return [];
                                    }

                                    return ProductVariant::where('product_id', $productId)
                                        ->pluck('name', 'id');
                                })
                                ->searchable()
                                ->disabled()
                                ->dehydrated()
                                ->visible(fn (callable $get) => $get('product_variant_id') !== null)
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('ordered_quantity')
                                ->label('Ordered Qty')
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
                                ->columnSpan(3),

                            Forms\Components\TextInput::make('received_quantity')
                                ->label('Received Qty')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->suffix(function (callable $get) {
                                    $productId = $get('product_id');
                                    if ($productId) {
                                        $product = Product::find($productId);

                                        return $product ? $product->unit : '';
                                    }

                                    return '';
                                })
                                ->helperText('Enter the actual quantity received')
                                ->columnSpan(3),

                            Forms\Components\Placeholder::make('variance')
                                ->label('Variance')
                                ->content(function (callable $get): string {
                                    $ordered = $get('ordered_quantity') ?? 0;
                                    $received = $get('received_quantity') ?? 0;
                                    $variance = $received - $ordered;

                                    if ($variance > 0) {
                                        return '+'.$variance.' (Surplus)';
                                    } elseif ($variance < 0) {
                                        return $variance.' (Shortage)';
                                    } else {
                                        return '0 (Match)';
                                    }
                                })
                                ->columnSpan(3),
                        ])
                        ->columns(6)
                        ->defaultItems(0)
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
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
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->visible(fn (callable $get) => ! empty($get('purchase_order_id'))),
        ]);
    }
}
