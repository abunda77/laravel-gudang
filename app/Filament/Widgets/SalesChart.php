<?php

namespace App\Filament\Widgets;

use App\Models\OutboundOperation;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected ?string $heading = 'Sales Last 7 Days';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            $count = OutboundOperation::whereDate('shipped_date', $date)->count();

            return [
                'date' => $date->format('M d'),
                'count' => $count,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Outbound Operations',
                    'data' => $data->pluck('count')->toArray(),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
