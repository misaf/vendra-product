<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use Misaf\VendraPermission\Tests\Support\PermissionModuleTestContext;
use Misaf\VendraProduct\Database\Factories\ProductCategoryFactory;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Pages\ListProductCategories;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\ListProducts;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    PermissionModuleTestContext::setUpFilamentAdminContext();

    Filament::getPanel('admin')->plugin(
        SpatieTranslatablePlugin::make()->defaultLocales(['en', 'de']),
    );
});

it('sorts the products table by every sortable column following the stored values', function (): void {
    $productCategory = ProductCategoryFactory::new()->createOne();

    $first = ProductFactory::new()->forCategory($productCategory)->createOne();
    $second = ProductFactory::new()->forCategory($productCategory)->createOne();

    expect(livewire(ListProducts::class)->call('loadTable'))
        ->toSortByEverySortableColumn([$first, $second]);
});

it('sorts the product categories table by every sortable column following the stored values', function (): void {
    $first = ProductCategoryFactory::new()->createOne();
    $second = ProductCategoryFactory::new()->createOne();

    expect(livewire(ListProductCategories::class)->call('loadTable'))
        ->toSortByEverySortableColumn([$first, $second]);
});
