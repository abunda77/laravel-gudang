<?php

namespace App\Filament\Resources\InvoiceResource\Schemas;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Services\DocumentGenerationService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

class InvoiceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['salesOrder.customer']))
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('salesOrder.so_number')
                    ->label('SO Number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('salesOrder.customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Invoice Date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable()
                    ->color(fn (Invoice $record): string => 
                        $record->due_date->isPast() && $record->payment_status === InvoiceStatus::UNPAID
                            ? 'danger'
                            : 'gray'
                    ),
                
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->color(fn (InvoiceStatus $state): string => match ($state) {
                        InvoiceStatus::PAID => 'success',
                        InvoiceStatus::UNPAID => 'warning',
                    }),
                
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('IDR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options(InvoiceStatus::class)
                    ->native(false),
                
                Tables\Filters\SelectFilter::make('customer')
                    ->label('Customer')
                    ->relationship('salesOrder.customer', 'name')
                    ->preload()
                    ->multiple(),
                
                Tables\Filters\Filter::make('invoice_date')
                    ->form([
                        Forms\Components\DatePicker::make('invoice_from')
                            ->label('Invoice Date From'),
                        Forms\Components\DatePicker::make('invoice_until')
                            ->label('Invoice Date Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['invoice_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('invoice_date', '>=', $date),
                            )
                            ->when(
                                $data['invoice_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('invoice_date', '<=', $date),
                            );
                    }),
                
                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue Invoices')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('payment_status', InvoiceStatus::UNPAID)
                            ->where('due_date', '<', now())
                    ),
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
