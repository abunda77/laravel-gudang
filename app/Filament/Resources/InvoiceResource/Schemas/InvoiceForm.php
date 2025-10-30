<?php

namespace App\Filament\Resources\InvoiceResource\Schemas;

use App\Enums\InvoiceStatus;
use App\Models\SalesOrder;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Invoice Information')
                ->schema([
                    Forms\Components\TextInput::make('invoice_number')
                        ->label('Invoice Number')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('Auto-generated')
                        ->helperText('Invoice number will be generated automatically')
                        ->columnSpan(1),
                    
                    Forms\Components\Select::make('sales_order_id')
                        ->label('Sales Order')
                        ->options(function () {
                            return SalesOrder::with('customer')
                                ->whereDoesntHave('invoices')
                                ->get()
                                ->mapWithKeys(function ($so) {
                                    return [$so->id => "{$so->so_number} - {$so->customer->name}"];
                                });
                        })
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $salesOrder = SalesOrder::find($state);
                                if ($salesOrder) {
                                    $set('total_amount', $salesOrder->total_amount);
                                }
                            }
                        })
                        ->helperText('Only sales orders without invoices are shown')
                        ->columnSpan(1),
                    
                    Forms\Components\DatePicker::make('invoice_date')
                        ->label('Invoice Date')
                        ->required()
                        ->default(now())
                        ->native(false)
                        ->columnSpan(1),
                    
                    Forms\Components\DatePicker::make('due_date')
                        ->label('Due Date')
                        ->required()
                        ->default(now()->addDays(30))
                        ->native(false)
                        ->minDate(fn (callable $get) => $get('invoice_date'))
                        ->columnSpan(1),
                ])
                ->columns(2),

            Section::make('Payment Information')
                ->schema([
                    Forms\Components\TextInput::make('total_amount')
                        ->label('Total Amount')
                        ->required()
                        ->numeric()
                        ->prefix('Rp')
                        ->disabled()
                        ->dehydrated()
                        ->helperText('Amount is automatically loaded from sales order')
                        ->columnSpan(1),
                    
                    Forms\Components\Select::make('payment_status')
                        ->label('Payment Status')
                        ->options(InvoiceStatus::class)
                        ->required()
                        ->default(InvoiceStatus::UNPAID)
                        ->native(false)
                        ->columnSpan(1),
                ])
                ->columns(2),

            Section::make('Invoice Items Preview')
                ->schema([
                    Forms\Components\Placeholder::make('items_preview')
                        ->label('')
                        ->content(function (callable $get) {
                            $salesOrderId = $get('sales_order_id');
                            if (!$salesOrderId) {
                                return 'Select a sales order to view items';
                            }
                            
                            $salesOrder = SalesOrder::with('items.product')->find($salesOrderId);
                            if (!$salesOrder || $salesOrder->items->isEmpty()) {
                                return 'No items found';
                            }
                            
                            $html = '<div class="overflow-x-auto">';
                            $html .= '<table class="min-w-full divide-y divide-gray-200">';
                            $html .= '<thead class="bg-gray-50">';
                            $html .= '<tr>';
                            $html .= '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>';
                            $html .= '<th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>';
                            $html .= '<th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>';
                            $html .= '<th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>';
                            $html .= '</tr>';
                            $html .= '</thead>';
                            $html .= '<tbody class="bg-white divide-y divide-gray-200">';
                            
                            foreach ($salesOrder->items as $item) {
                                $subtotal = $item->quantity * $item->unit_price;
                                $html .= '<tr>';
                                $html .= '<td class="px-3 py-2 text-sm">' . e($item->product->name) . '</td>';
                                $html .= '<td class="px-3 py-2 text-sm text-right">' . number_format($item->quantity, 0) . '</td>';
                                $html .= '<td class="px-3 py-2 text-sm text-right">Rp ' . number_format($item->unit_price, 0) . '</td>';
                                $html .= '<td class="px-3 py-2 text-sm text-right">Rp ' . number_format($subtotal, 0) . '</td>';
                                $html .= '</tr>';
                            }
                            
                            $html .= '</tbody>';
                            $html .= '</table>';
                            $html .= '</div>';
                            
                            return new \Illuminate\Support\HtmlString($html);
                        }),
                ])
                ->collapsible()
                ->collapsed(false),
        ]);
    }
}
