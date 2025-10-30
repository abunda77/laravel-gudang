<?php

namespace App\Filament\Widgets;

use App\Models\OutboundOperation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOutboundTable extends BaseWidget
{
    protected static ?int $sort = 7;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent Outbound Operations')
            ->query(
                OutboundOperation::query()
                    ->with(['salesOrder', 'preparer'])
                    ->latest('shipped_date')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('outbound_number')
                    ->label('Outbound Number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('salesOrder.so_number')
                    ->label('SO Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('shipped_date')
                    ->label('Shipped Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('preparer.name')
                    ->label('Prepared By')
                    ->default('N/A'),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->default('Outbound')
                    ->color('warning'),
            ])
            ->paginated([5, 10]);
    }
}
