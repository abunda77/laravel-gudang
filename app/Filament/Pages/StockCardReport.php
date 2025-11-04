<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\StockMovement;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockCardReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static string | \UnitEnum | null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Stock Card';

    protected static ?string $title = 'Stock Card Report';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'reports/stock-card';

    protected string $view = 'filament.pages.stock-card-report';

    public ?int $product = null;

    public function mount(): void
    {
        $this->product = request()->query('product');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                StockMovement::query()
                    ->with(['product', 'creator'])
                    ->when($this->product, fn(Builder $query) => $query->where('product_id', $this->product))
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->size('sm')
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->toggleable(isToggledHiddenByDefault: $this->product !== null),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: $this->product !== null),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn($state): string => match ($state) {
                        \App\Enums\StockMovementType::INBOUND => 'INBOUND',
                        \App\Enums\StockMovementType::OUTBOUND => 'OUTBOUND',
                        \App\Enums\StockMovementType::ADJUSTMENT_PLUS => 'ADJUSTMENT +',
                        \App\Enums\StockMovementType::ADJUSTMENT_MINUS => 'ADJUSTMENT -',
                        default => strtoupper($state->value ?? $state),
                    })
                    ->color(fn($state): string => match ($state) {
                        \App\Enums\StockMovementType::INBOUND => 'success',
                        \App\Enums\StockMovementType::OUTBOUND => 'danger',
                        \App\Enums\StockMovementType::ADJUSTMENT_PLUS => 'warning',
                        \App\Enums\StockMovementType::ADJUSTMENT_MINUS => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn($state): string => match ($state) {
                        \App\Enums\StockMovementType::INBOUND => 'heroicon-o-arrow-down-tray',
                        \App\Enums\StockMovementType::OUTBOUND => 'heroicon-o-arrow-up-tray',
                        \App\Enums\StockMovementType::ADJUSTMENT_PLUS => 'heroicon-o-adjustments-horizontal',
                        \App\Enums\StockMovementType::ADJUSTMENT_MINUS => 'heroicon-o-adjustments-horizontal',
                        default => 'heroicon-o-question-mark-circle',
                    }),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty')
                    ->alignCenter()
                    ->badge()
                    ->size('lg')
                    ->color(fn(int $state): string => $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn(int $state): string => ($state > 0 ? '+' : '') . number_format($state)),

                Tables\Columns\TextColumn::make('running_balance')
                    ->label('Balance')
                    ->alignCenter()
                    ->getStateUsing(function (StockMovement $record): int {
                        return StockMovement::where('product_id', $record->product_id)
                            ->where('created_at', '<=', $record->created_at)
                            ->sum('quantity');
                    })
                    ->badge()
                    ->size('lg')
                    ->color('info')
                    ->formatStateUsing(fn(int $state): string => number_format($state)),

                Tables\Columns\TextColumn::make('reference_type')
                    ->label('Reference')
                    ->formatStateUsing(fn(?string $state): string => $state ? class_basename($state) : '-')
                    ->description(fn(StockMovement $record): ?string => $record->reference_id ? '#' . $record->reference_id : null)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->wrap()
                    ->limit(40)
                    ->placeholder('-')
                    ->tooltip(fn(?string $state): ?string => $state)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('By')
                    ->placeholder('System')
                    ->icon('heroicon-o-user')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Movement Type')
                    ->options([
                        'inbound' => 'Inbound',
                        'outbound' => 'Outbound',
                        'adjustment_plus' => 'Adjustment (+)',
                        'adjustment_minus' => 'Adjustment (-)',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->label('Date Range')
                    ->form([
                        DatePicker::make('from')
                            ->label('From Date'),
                        DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
