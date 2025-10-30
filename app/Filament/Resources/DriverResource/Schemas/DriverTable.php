<?php

namespace App\Filament\Resources\DriverResource\Schemas;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Tables;
use Filament\Tables\Table;

class DriverTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-avatar.png')),

                Tables\Columns\TextColumn::make('name')
                    ->label('Driver Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->copyable(),

                Tables\Columns\TextColumn::make('id_card_number')
                    ->label('ID Card')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('deliveryOrders_count')
                    ->label('Total Deliveries')
                    ->counts('deliveryOrders')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
