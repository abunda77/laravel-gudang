<?php

namespace App\Filament\Widgets;

use App\Models\InboundOperation;
use App\Models\OutboundOperation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentActivityTable extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent Inbound Operations')
            ->query(
                InboundOperation::query()
                    ->with(['purchaseOrder', 'receiver'])
                    ->latest('received_date')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('inbound_number')
                    ->label('Inbound Number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchaseOrder.po_number')
                    ->label('PO Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('received_date')
                    ->label('Received Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('receiver.name')
                    ->label('Received By')
                    ->default('N/A'),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->default('Inbound')
                    ->color('info'),
            ])
            ->paginated([5, 10]);
    }
}
