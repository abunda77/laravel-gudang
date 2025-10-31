<?php

namespace App\Filament\Resources\PurchaseOrderResource\Schemas;

use App\Enums\PurchaseOrderStatus;
use App\Models\Product;
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
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('unit_price', $product->purchase_price);
                                        }
                                    }
                                })
                                ->disableOptionWhen(function ($value, $state, callable $get) {
                                    // Disable products selected in other repeater rows (allow the current row's selection)
                                    $items = $get('../../items') ?? [];

                                    $selected = collect($items)
                                        ->pluck('product_id')
                                        ->filter()
                                        ->values();

                                    // Exclude the current field's selected value to avoid self-disabling
                                    if ($state !== null) {
                                        $index = $selected->search($state, true);
                                        if ($index !== false) {
                                            $selected->forget($index);
                                        }
                                    }

                                    return $selected->containsStrict($value);
                                })
                                ->columnSpan(2),

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
                        ->columns(5)
                        ->defaultItems(1)
                        ->addActionLabel('Add Product')
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => isset($state['product_id'])
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
