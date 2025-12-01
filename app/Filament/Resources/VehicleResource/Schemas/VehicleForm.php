<?php

namespace App\Filament\Resources\VehicleResource\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Vehicle Information')
                ->schema([
                    Forms\Components\TextInput::make('license_plate')
                        ->label('License Plate')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50)
                        ->placeholder('e.g., B 1234 XYZ')
                        ->columnSpan(1),
                    
                    Forms\Components\Select::make('vehicle_type')
                        ->label('Vehicle Type')
                        ->options([
                            'truck' => 'Truck',
                            'van' => 'Van',
                            'motor' => 'Motor',
                            'mobil' => 'Mobil',
                        ])
                        ->required()
                        ->native(false)
                        ->columnSpan(1),
                    
                    Forms\Components\Select::make('ownership_status')
                        ->label('Ownership Status')
                        ->options([
                            'owned' => 'Owned',
                            'rented' => 'Rented',
                        ])
                        ->required()
                        ->native(false)
                        ->columnSpan(2),
                ])
                ->columns(2),
        ]);
    }
}
