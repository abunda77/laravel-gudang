<?php

namespace App\Filament\Resources\PurchaseOrderResource\Schemas;

use App\Enums\PurchaseOrderStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PurchaseOrderTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['supplier', 'creator']))
            ->columns([
                Tables\Columns\TextColumn::make('po_number')
                    ->label('PO Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('order_date')
                    ->label('Order Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('expected_date')
                    ->label('Expected Date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        PurchaseOrderStatus::DRAFT->value => 'Draft',
                        PurchaseOrderStatus::SENT->value => 'Sent',
                        PurchaseOrderStatus::PARTIALLY_RECEIVED->value => 'Partially Received',
                        PurchaseOrderStatus::COMPLETED->value => 'Completed',
                        PurchaseOrderStatus::CANCELLED->value => 'Cancelled',
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

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
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
                        PurchaseOrderStatus::DRAFT->value => 'Draft',
                        PurchaseOrderStatus::SENT->value => 'Sent',
                        PurchaseOrderStatus::PARTIALLY_RECEIVED->value => 'Partially Received',
                        PurchaseOrderStatus::COMPLETED->value => 'Completed',
                        PurchaseOrderStatus::CANCELLED->value => 'Cancelled',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('supplier')
                    ->relationship('supplier', 'name')
                    ->label('Supplier')
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
                                fn(Builder $query, $date): Builder => $query->whereDate('order_date', '>=', $date),
                            )
                            ->when(
                                $data['order_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('order_date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->infolist([
                        Section::make('Purchase Order Information')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('po_number')
                                            ->label('PO Number')
                                            ->weight(FontWeight::Bold)
                                            ->copyable(),

                                        TextEntry::make('supplier.name')
                                            ->label('Supplier')
                                            ->weight(FontWeight::SemiBold),

                                        TextEntry::make('status')
                                            ->label('Status')
                                            ->badge()
                                            ->color(fn($state): string => match ($state?->value ?? $state) {
                                                'draft' => 'gray',
                                                'sent' => 'info',
                                                'partially_received' => 'warning',
                                                'completed' => 'success',
                                                'cancelled' => 'danger',
                                                default => 'gray',
                                            }),
                                    ]),

                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('order_date')
                                            ->label('Order Date')
                                            ->date('d M Y'),

                                        TextEntry::make('expected_date')
                                            ->label('Expected Delivery Date')
                                            ->date('d M Y'),

                                        TextEntry::make('total_amount')
                                            ->label('Total Amount')
                                            ->money('IDR')
                                            ->weight(FontWeight::Bold)
                                            ->size('lg'),
                                    ]),

                                TextEntry::make('notes')
                                    ->label('Notes')
                                    ->placeholder('No notes')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Order Items')
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
                                                TextEntry::make('ordered_quantity')
                                                    ->label('Quantity')
                                                    ->suffix(' units'),

                                                TextEntry::make('unit_price')
                                                    ->label('Unit Price')
                                                    ->money('IDR'),

                                                TextEntry::make('subtotal')
                                                    ->label('Subtotal')
                                                    ->money('IDR')
                                                    ->weight(FontWeight::Bold)
                                                    ->state(fn($record) => $record->ordered_quantity * $record->unit_price),
                                            ]),
                                    ])
                                    ->contained(false),
                            ])
                            ->collapsible(),

                        Section::make('Additional Information')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('creator.name')
                                            ->label('Created By'),

                                        TextEntry::make('created_at')
                                            ->label('Created At')
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
