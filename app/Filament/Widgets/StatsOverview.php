<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\InboundOperation;
use App\Models\OutboundOperation;
use App\Models\Product;
use App\Services\StockMovementService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $stockService = app(StockMovementService::class);

        // Calculate total stock value with 5-minute cache
        $totalStockValue = Cache::remember('dashboard_total_stock_value', 300, function () use ($stockService) {
            return Product::all()->sum(function ($product) use ($stockService) {
                return $stockService->getCurrentStock($product) * $product->purchase_price;
            });
        });

        // Get total customer count with 5-minute cache
        $totalCustomers = Cache::remember('dashboard_total_customers', 300, function () {
            return Customer::count();
        });

        // Get today's inbound operation count with 5-minute cache
        $inboundToday = Cache::remember('dashboard_inbound_today_' . today()->format('Y-m-d'), 300, function () {
            return InboundOperation::whereDate('received_date', today())->count();
        });

        // Get today's outbound operation count with 5-minute cache
        $outboundToday = Cache::remember('dashboard_outbound_today_' . today()->format('Y-m-d'), 300, function () {
            return OutboundOperation::whereDate('shipped_date', today())->count();
        });

        return [
            Stat::make('Total Stock Value', 'Rp ' . number_format($totalStockValue, 0, ',', '.'))
                ->description('Current inventory value')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Total Customers', number_format($totalCustomers))
                ->description('Registered customers')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Inbound Today', $inboundToday)
                ->description('Goods received today')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('info'),

            Stat::make('Outbound Today', $outboundToday)
                ->description('Goods shipped today')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('warning'),
        ];
    }
}
