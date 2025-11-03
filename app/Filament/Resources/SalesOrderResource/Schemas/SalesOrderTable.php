<?php

namespace App\Filament\Resources\SalesOrderResource\Schemas;

use App\Enums\SalesOrderStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalesOrderTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['customer', 'salesUser']))
            ->columns([
                Tables\Columns\TextColumn::make('so_number')
                    ->label('SO Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('order_date')
                    ->label('Order Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        SalesOrderStatus::DRAFT->value => 'Draft',
                        SalesOrderStatus::APPROVED->value => 'Approved',
                        SalesOrderStatus::PARTIALLY_FULFILLED->value => 'Partially Fulfilled',
                        SalesOrderStatus::COMPLETED->value => 'Completed',
                        SalesOrderStatus::CANCELLED->value => 'Cancelled',
                    ])
                    ->selectablePlaceholder(false)
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('IDR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR')
                            ->label('Total'),
                    ]),

                Tables\Columns\TextColumn::make('salesUser.name')
                    ->label('Sales Person')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        SalesOrderStatus::DRAFT->value => 'Draft',
                        SalesOrderStatus::APPROVED->value => 'Approved',
                        SalesOrderStatus::PARTIALLY_FULFILLED->value => 'Partially Fulfilled',
                        SalesOrderStatus::COMPLETED->value => 'Completed',
                        SalesOrderStatus::CANCELLED->value => 'Cancelled',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('customer')
                    ->relationship('customer', 'name')
                    ->label('Customer')
                    ->preload()
                    ->multiple(),

                Tables\Filters\Filter::make('order_date')
                    ->form([
                        Forms\Components\DatePicker::make('order_from')
                            ->label('Order Date From'),
                        Forms\Components\DatePicker::make('order_until')
                            ->label('Order Date Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['order_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('order_date', '>=', $date),
                            )
                            ->when(
                                $data['order_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('order_date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->infolist([
                        \Filament\Schemas\Components\Section::make('Sales Order Information')
                            ->schema([
                                \Filament\Schemas\Components\Grid::make(3)
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('so_number')
                                            ->label('SO Number')
                                            ->weight(\Filament\Support\Enums\FontWeight::Bold)
                                            ->copyable(),

                                        \Filament\Infolists\Components\TextEntry::make('customer.name')
                                            ->label('Customer')
                                            ->weight(\Filament\Support\Enums\FontWeight::SemiBold),

                                        \Filament\Infolists\Components\TextEntry::make('status')
                                            ->label('Status')
                                            ->badge()
                                            ->color(fn ($state): string => match ($state?->value ?? $state) {
                                                'draft' => 'gray',
                                                'approved' => 'info',
                                                'partially_fulfilled' => 'warning',
                                                'completed' => 'success',
                                                'cancelled' => 'danger',
                                                default => 'gray',
                                            }),
                                    ]),

                                \Filament\Schemas\Components\Grid::make(3)
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('order_date')
                                            ->label('Order Date')
                                            ->date('d M Y'),

                                        \Filament\Infolists\Components\TextEntry::make('salesUser.name')
                                            ->label('Sales Person'),

                                        \Filament\Infolists\Components\TextEntry::make('total_amount')
                                            ->label('Total Amount')
                                            ->money('IDR')
                                            ->weight(\Filament\Support\Enums\FontWeight::Bold)
                                            ->size('lg'),
                                    ]),

                                \Filament\Infolists\Components\TextEntry::make('notes')
                                    ->label('Notes')
                                    ->placeholder('No notes')
                                    ->columnSpanFull(),
                            ]),

                        \Filament\Schemas\Components\Section::make('Order Items')
                            ->schema([
                                \Filament\Infolists\Components\RepeatableEntry::make('items')
                                    ->label('')
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('product.name')
                                            ->label('Product')
                                            ->weight(\Filament\Support\Enums\FontWeight::SemiBold),

                                        \Filament\Infolists\Components\TextEntry::make('productVariant.name')
                                            ->label('Variant')
                                            ->placeholder('No variant')
                                            ->color('gray'),

                                        \Filament\Schemas\Components\Grid::make(3)
                                            ->schema([
                                                \Filament\Infolists\Components\TextEntry::make('quantity')
                                                    ->label('Quantity')
                                                    ->suffix(' units'),

                                                \Filament\Infolists\Components\TextEntry::make('unit_price')
                                                    ->label('Unit Price')
                                                    ->money('IDR'),

                                                \Filament\Infolists\Components\TextEntry::make('subtotal')
                                                    ->label('Subtotal')
                                                    ->money('IDR')
                                                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                                                    ->state(fn ($record) => $record->quantity * $record->unit_price),
                                            ]),
                                    ])
                                    ->contained(false),
                            ])
                            ->collapsible(),

                        \Filament\Schemas\Components\Section::make('Additional Information')
                            ->schema([
                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('created_at')
                                            ->label('Created At')
                                            ->dateTime('d M Y, H:i'),

                                        \Filament\Infolists\Components\TextEntry::make('updated_at')
                                            ->label('Last Updated')
                                            ->dateTime('d M Y, H:i'),
                                    ]),
                            ])
                            ->collapsed()
                            ->collapsible(),
                    ])
                    ->modalWidth('5xl'),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
