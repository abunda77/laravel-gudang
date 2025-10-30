<?php

namespace App\Filament\Resources\SupplierResource\Schemas;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Tables;
use Filament\Tables\Table;

class SupplierTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Supplier Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('contact')
                    ->label('Contact Person')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('address')
                    ->label('Address')
                    ->searchable()
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\TextColumn::make('bank_account')
                    ->label('Bank Account')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),

                Tables\Columns\TextColumn::make('purchaseOrders_count')
                    ->label('Total POs')
                    ->counts('purchaseOrders')
                    ->badge()
                    ->color('warning')
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
