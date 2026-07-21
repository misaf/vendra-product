<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Widgets;

use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Misaf\VendraProduct\Models\Product;

final class ProductOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();

        $products = Product::query();
        $productTrend = Trend::query(clone $products)
            ->between($startDate, $endDate)
            ->perDay()
            ->count();

        $inStockProducts = Product::query()->where('in_stock', true);
        $inStockTrend = Trend::query(clone $inStockProducts)
            ->between($startDate, $endDate)
            ->perDay()
            ->count();

        $outOfStockProducts = Product::query()->where('in_stock', false);
        $outOfStockTrend = Trend::query(clone $outOfStockProducts)
            ->between($startDate, $endDate)
            ->perDay()
            ->count();

        return [
            Stat::make('total_product_stats', Number::format($products->count()))
                ->chart($this->chartValues($productTrend))
                ->color('primary')
                ->description(__('vendra-product::widgets.total_products_description'))
                ->descriptionIcon(Heroicon::Cube, IconPosition::Before)
                ->icon(Heroicon::OutlinedSquares2x2)
                ->label(__('vendra-product::widgets.total_products')),
            Stat::make('in_stock_product_stats', Number::format($inStockProducts->count()))
                ->chart($this->chartValues($inStockTrend))
                ->color('success')
                ->description(__('vendra-product::widgets.in_stock_products_description'))
                ->descriptionIcon(Heroicon::CheckCircle, IconPosition::Before)
                ->icon(Heroicon::OutlinedSquares2x2)
                ->label(__('vendra-product::widgets.in_stock_products')),
            Stat::make('out_of_stock_product_stats', Number::format($outOfStockProducts->count()))
                ->chart($this->chartValues($outOfStockTrend))
                ->color('danger')
                ->description(__('vendra-product::widgets.out_of_stock_products_description'))
                ->descriptionIcon(Heroicon::XCircle, IconPosition::Before)
                ->icon(Heroicon::OutlinedSquares2x2)
                ->label(__('vendra-product::widgets.out_of_stock_products')),
        ];
    }

    /**
     * @param  Collection<(int|string), mixed>  $values
     * @return list<float>
     */
    private function chartValues(Collection $values): array
    {
        $chart = [];

        foreach ($values as $value) {
            if ($value instanceof TrendValue && is_numeric($value->aggregate)) {
                $chart[] = (float) $value->aggregate;
            }
        }

        return $chart;
    }
}
