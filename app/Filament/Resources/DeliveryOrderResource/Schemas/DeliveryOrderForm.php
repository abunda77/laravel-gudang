<?php

namespace App\Filament\Resources\DeliveryOrderResource\Schemas;

use App\Models\Driver;
use App\Models\OutboundOperation;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DeliveryOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Delivery Order Information')
                ->schema([
                    Forms\Components\TextInput::make('do_number')
                        ->label('DO Number')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('Auto-generated')
                        ->helperText('Delivery order number will be generated automatically')
                        ->columnSpan(1),
                    
                    Forms\Components\Select::make('outbound_operation_id')
                        ->label('Outbound Operation')
                        ->options(function () {
                            return OutboundOperation::with(['salesOrder.customer', 'stockMovements'])
                                ->get()
                                ->filter(function ($outbound) {
                                    // Only show outbound operations that have stock recorded
                                    // and don't already have a delivery order
                                    return $outbound->stockMovements()->exists() 
                                        && !$outbound->deliveryOrder()->exists();
                                })
                                ->mapWithKeys(function ($outbound) {
                                    return [$outbound->id => "{$outbound->outbound_number} - {$outbound->salesOrder->customer->name}"];
                                });
                        })
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $outbound = OutboundOperation::with('salesOrder.customer')->find($state);
                                if ($outbound && $outbound->salesOrder && $outbound->salesOrder->customer) {
                                    $set('recipient_name', $outbound->salesOrder->customer->name);
                                }
                            }
                        })
                        ->helperText('Only confirmed outbound operations without delivery orders are shown')
                        ->columnSpan(1),
                    
                    Forms\Components\DateTimePicker::make('delivery_date')
                        ->label('Delivery Date')
                        ->required()
                        ->default(now())
                        ->native(false)
                        ->columnSpan(1),
                    
                    Forms\Components\TextInput::make('recipient_name')
                        ->label('Recipient Name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),
                ])
                ->columns(2),

            Section::make('Delivery Assignment')
                ->schema([
                    Forms\Components\Select::make('driver_id')
                        ->label('Driver')
                        ->options(Driver::query()->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpan(1),
                    
                    Forms\Components\Select::make('vehicle_id')
                        ->label('Vehicle')
                        ->options(function () {
                            return Vehicle::query()
                                ->get()
                                ->mapWithKeys(function ($vehicle) {
                                    return [$vehicle->id => "{$vehicle->license_plate} ({$vehicle->vehicle_type})"];
                                });
                        })
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpan(1),
                    
                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Barcode')
                ->schema([
                    Forms\Components\Placeholder::make('barcode_info')
                        ->label('')
                        ->content('Barcode will be generated automatically when the delivery order is created.')
                        ->visible(fn ($record) => !$record),
                    
                    Forms\Components\ViewField::make('barcode')
                        ->label('Barcode')
                        ->view('filament.forms.components.barcode-display')
                        ->visible(fn ($record) => $record && $record->barcode),
                ])
                ->collapsible()
                ->collapsed(fn ($record) => !$record),
        ]);
    }
}
