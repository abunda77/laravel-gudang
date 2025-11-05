<?php

namespace App\Filament\Resources\StockOpnameResource\Schemas;

use App\Models\StockOpname;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;

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
                    ->color(fn (int $state): string => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray')
                    )
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? '+'.$state : (string) $state
                    ),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('is_confirmed')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function (StockOpname $record): string {
                        return $record->stockMovements()->exists() ? 'Confirmed' : 'Pending';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Confirmed' => 'success',
                        'Pending' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Confirmed' => 'heroicon-o-check-circle',
                        'Pending' => 'heroicon-o-clock',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->action(function (StockOpname $record) {
                        // Only allow action if not yet confirmed
                        if ($record->stockMovements()->exists()) {
                            return;
                        }
                        
                        // Redirect to view page where confirm action is available
                        return redirect()->route('filament.admin.resources.stock-opnames.view', ['record' => $record]);
                    })
                    ->tooltip(fn (StockOpname $record): ?string => 
                        !$record->stockMovements()->exists() ? 'Click to confirm' : null
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('confirmed')
                    ->label('Confirmed Only')
                    ->query(fn ($query) => $query->whereHas('stockMovements')
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('pending')
                    ->label('Pending Confirmation')
                    ->query(fn ($query) => $query->whereDoesntHave('stockMovements')
                    )
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->infolist([
                        Section::make('Stock Opname Information')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('opname_number')
                                            ->label('Opname Number')
                                            ->weight(FontWeight::Bold)
                                            ->copyable(),

                                        TextEntry::make('opname_date')
                                            ->label('Opname Date')
                                            ->date('d M Y'),

                                        IconEntry::make('is_confirmed')
                                            ->label('Status')
                                            ->boolean()
                                            ->state(fn ($record) => $record->stockMovements()->exists())
                                            ->trueIcon('heroicon-o-check-circle')
                                            ->falseIcon('heroicon-o-clock')
                                            ->trueColor('success')
                                            ->falseColor('warning')
                                            ->label(fn ($record) => $record->stockMovements()->exists() ? 'Confirmed' : 'Pending'),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('creator.name')
                                            ->label('Created By'),

                                        TextEntry::make('items_count')
                                            ->label('Products Counted')
                                            ->state(fn ($record) => $record->items->count())
                                            ->badge()
                                            ->color('info'),
                                    ]),

                                TextEntry::make('notes')
                                    ->label('Notes')
                                    ->placeholder('No notes')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Stock Count Details')
                            ->schema([
                                RepeatableEntry::make('items')
                                    ->label('')
                                    ->schema([
                                        TextEntry::make('product.name')
                                            ->label('Product')
                                            ->weight(FontWeight::SemiBold),

                                        TextEntry::make('productVariant.name')
                                            ->label('Variant')
                                            ->placeholder('No variant')
                                            ->color('gray'),

                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('system_stock')
                                                    ->label('System Stock')
                                                    ->suffix(fn ($record) => ' '.$record->product->unit),

                                                TextEntry::make('physical_stock')
                                                    ->label('Physical Stock')
                                                    ->suffix(fn ($record) => ' '.$record->product->unit),

                                                TextEntry::make('variance')
                                                    ->label('Variance')
                                                    ->formatStateUsing(fn ($state, $record) => ($state > 0 ? '+' : '').$state.' '.$record->product->unit
                                                    )
                                                    ->badge()
                                                    ->color(fn ($state) => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray')
                                                    ),
                                            ]),
                                    ])
                                    ->contained(false),
                            ])
                            ->collapsible(),

                        Section::make('Additional Information')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('created_at')
                                            ->label('Created At')
                                            ->dateTime('d M Y, H:i'),

                                        TextEntry::make('updated_at')
                                            ->label('Last Updated')
                                            ->dateTime('d M Y, H:i'),
                                    ]),
                            ])
                            ->collapsed()
                            ->collapsible(),
                    ])
                    ->modalWidth('5xl'),

                EditAction::make()
                    ->visible(fn (StockOpname $record): bool => ! $record->stockMovements()->exists()),

                DeleteAction::make()
                    ->visible(fn (StockOpname $record): bool => ! $record->stockMovements()->exists()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if (! $record->stockMovements()->exists()) {
                                    $record->delete();
                                }
                            });
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
