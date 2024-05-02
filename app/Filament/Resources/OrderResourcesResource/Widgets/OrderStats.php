<?php

namespace App\Filament\Resources\OrderResourcesResource\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStats extends BaseWidget
{

    protected function getStats(): array
    {
        $total = Order::query()->avg('grand_total');
        return [
            Stat::make('Orders', Order::count()),
            Stat::make('Pending Orders', Order::where('status', 'processing')->count()),
            Stat::make('Shipping Orders', Order::where('status', 'shipping')->count()),
            Stat::make('Completed Orders', Order::where('status', 'completed')->count()),
            Stat::make('Canceled Orders', Order::where('status', 'canceled')->count()),
            Stat::make('Average Orders', "Rp. " . number_format($total, 0, ',', '.')),
        ];
    }
}