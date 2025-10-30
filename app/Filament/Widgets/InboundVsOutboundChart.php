<?php

namespace App\Filament\Widgets;

use App\Models\InboundOperation;
use App\Models\OutboundOperation;
use Filament\Widgets\ChartWidget;

class InboundVsOutboundChart extends ChartWidget
{
    protected ?string $heading = 'Inbound vs Outbound Operations';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            $inboundCount = InboundOperation::whereDate('received_date', $date)->count();
            $outboundCount = OutboundOperation::whereDate('shipped_date', $date)->count();

            return [
                'date' => $date->format('M d'),
                'inbound' => $inboundCount,
                'outbound' => $outboundCount,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Inbound',
                    'data' => $data->pluck('inbound')->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                ],
                [
                    'label' => 'Outbound',
                    'data' => $data->pluck('outbound')->toArray(),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.5)',
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
