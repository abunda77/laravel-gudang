<?php

namespace App\Filament\Resources\StockOpnameResource\Schemas;

use App\Models\StockOpname;
use App\Services\StockMovementService;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class StockOpnameTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['items', 'creator', 'stockMovements']))
            ->columns([
                Tables\Columns\TextColumn::make('opname_number')
                    ->label('Opname Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('opname_date')
                    ->label('Opname Date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Products Counted')
                    ->counts('items')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('total_variance')
                    ->label('Total Variance')
                    ->getStateUsing(function (StockOpname $record): int {
                        return $record->items->sum('variance');
                    })
                    ->badge()
                    ->color(fn (int $state): string => 
                        $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray')
                    )
                    ->formatStateUsing(fn (int $state): string => 
                        $state > 0 ? '+' . $state : (string) $state
                    ),
                
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\IconColumn::make('is_confirmed')
                    ->label('Confirmed')
                    ->boolean()
                    ->getStateUsing(function (StockOpname $record): bool {
                        return $record->stockMovements()->exists();
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('confirmed')
                    ->label('Confirmed Only')
                    ->query(fn ($query) => 
                        $query->whereHas('stockMovements')
                    )
                    ->toggle(),
                
                Tables\Filters\Filter::make('pending')
                    ->label('Pending Confirmation')
                    ->query(fn ($query) => 
                        $query->whereDoesntHave('stockMovements')
                    )
                    ->toggle(),
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
