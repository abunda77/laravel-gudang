<?php

namespace App\Filament\Resources\DeliveryOrderResource\Schemas;

use App\Models\DeliveryOrder;
use App\Services\DocumentGenerationService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeliveryOrderTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['outboundOperation.salesOrder.customer', 'driver', 'vehicle']))
            ->columns([
                Tables\Columns\TextColumn::make('do_number')
                    ->label('DO Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('outboundOperation.outbound_number')
                    ->label('Outbound Number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('outboundOperation.salesOrder.customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('driver.name')
                    ->label('Driver')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('vehicle.license_plate')
                    ->label('Vehicle')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (DeliveryOrder $record): string => 
                        "{$record->vehicle->license_plate} ({$record->vehicle->vehicle_type})"
                    ),
                
                Tables\Columns\TextColumn::make('delivery_date')
                    ->label('Delivery Date')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('recipient_name')
                    ->label('Recipient')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('driver')
                    ->relationship('driver', 'name')
                    ->label('Driver')
                    ->preload()
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('vehicle')
                    ->relationship('vehicle', 'license_plate')
                    ->label('Vehicle')
                    ->preload()
                    ->multiple(),
                
                Tables\Filters\Filter::make('delivery_date')
                    ->form([
                        Forms\Components\DatePicker::make('delivery_from')
                            ->label('Delivery Date From'),
                        Forms\Components\DatePicker::make('delivery_until')
                            ->label('Delivery Date Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['delivery_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('delivery_date', '>=', $date),
                            )
                            ->when(
                                $data['delivery_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('delivery_date', '<=', $date),
                            );
                    }),
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
