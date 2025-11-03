<?php

namespace App\Filament\Resources\InvoiceResource\Schemas;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Services\DocumentGenerationService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

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
                
                Tables\Columns\SelectColumn::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        InvoiceStatus::PAID->value => 'Paid',
                        InvoiceStatus::UNPAID->value => 'Unpaid',
                        InvoiceStatus::OVERDUE->value => 'Overdue',
                    ])
                    ->selectablePlaceholder(false)
                    ->sortable(),
                
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
                    ->options([
                        InvoiceStatus::PAID->value => 'Paid',
                        InvoiceStatus::UNPAID->value => 'Unpaid',
                        InvoiceStatus::OVERDUE->value => 'Overdue',
                    ])
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
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('printInvoice')
                    ->label('Print Invoice')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->action(function (Invoice $record) {
                        try {
                            $invoice = $record->load(['salesOrder.customer', 'salesOrder.items.product']);

                            $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));

                            return response()->streamDownload(function () use ($pdf) {
                                echo $pdf->output();
                            }, "invoice-{$record->invoice_number}.pdf");
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error generating PDF')
                                ->body('Failed to generate invoice PDF: ' . $e->getMessage())
                                ->danger()
                                ->send();

                            return;
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
