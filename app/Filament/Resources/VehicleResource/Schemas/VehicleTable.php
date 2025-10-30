<?php

namespace App\Filament\Resources\VehicleResource\Schemas;

use Filament\Tables;
use Filament\Tables\Table;

class VehicleTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('license_plate')
                    ->label('License Plate')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('vehicle_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'truck' => 'success',
                        'van' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('ownership_status')
                    ->label('Ownership')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'owned' => 'success',
                        'rented' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('deliveryOrders_count')
                    ->label('Total Deliveries')
                    ->counts('deliveryOrders')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vehicle_type')
                    ->label('Vehicle Type')
                    ->options([
                        'truck' => 'Truck',
                        'van' => 'Van',
                    ])
                    ->native(false),
                
                Tables\Filters\SelectFilter::make('ownership_status')
                    ->label('Ownership Status')
                    ->options([
                        'owned' => 'Owned',
                        'rented' => 'Rented',
                    ])
                    ->native(false),
            ])
            // ->actions([
            //     Tables\Actions\EditAction::make(),
            //     Tables\Actions\DeleteAction::make(),
            // ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ])
            ->defaultSort('created_at', 'desc');
    }
}
