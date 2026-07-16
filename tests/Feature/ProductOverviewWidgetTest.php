<?php

declare(strict_types=1);

use Filament\Panel;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Misaf\VendraProduct\Database\Factories\ProductCategoryFactory;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\ListProducts;
use Misaf\VendraProduct\Filament\Widgets\ProductOverviewWidget;
use Misaf\VendraProduct\ProductPlugin;
use Misaf\VendraTenant\Database\Factories\TenantFactory;

use function Pest\Livewire\livewire;

it('registers the product overview widget on the product panel', function (): void {
    $panel = Panel::make()->id('admin');

    ProductPlugin::make()->register($panel);

    expect($panel->getWidgets())->toContain(ProductOverviewWidget::class);
});

it('shows the product overview widget on the product list page', function (): void {
    $headerWidgets = (new ReflectionMethod(ListProducts::class, 'getHeaderWidgets'))
        ->invoke(app(ListProducts::class));

    expect($headerWidgets)->toBe([
        ProductOverviewWidget::class,
    ]);
});

it('shows tenant-scoped product inventory metrics', function (): void {
    $tenant = TenantFactory::new()->enabled()->createOne();
    $tenant->makeCurrent();

    $category = ProductCategoryFactory::new()->createOne();

    ProductFactory::new()
        ->forCategory($category)
        ->count(2)
        ->create(['in_stock' => true]);

    ProductFactory::new()
        ->forCategory($category)
        ->create(['in_stock' => false]);

    $otherTenant = TenantFactory::new()->enabled()->createOne();
    $otherTenant->makeCurrent();

    $otherCategory = ProductCategoryFactory::new()->createOne();

    ProductFactory::new()
        ->forCategory($otherCategory)
        ->count(4)
        ->create(['in_stock' => true]);

    $tenant->makeCurrent();

    livewire(ProductOverviewWidget::class)
        ->assertSeeInOrder([
            __('vendra-product::widgets.total_products'),
            '3',
            __('vendra-product::widgets.total_products_description'),
            __('vendra-product::widgets.in_stock_products'),
            '2',
            __('vendra-product::widgets.in_stock_products_description'),
            __('vendra-product::widgets.out_of_stock_products'),
            '1',
            __('vendra-product::widgets.out_of_stock_products_description'),
        ]);

    /** @var array<int, Stat> $stats */
    $stats = (new ReflectionMethod(ProductOverviewWidget::class, 'getStats'))
        ->invoke(app(ProductOverviewWidget::class));

    expect(array_map(
        static fn(Stat $stat): mixed => $stat->getIcon(),
        $stats,
    ))->each->toBe(Heroicon::OutlinedSquares2x2);
});
