<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use Misaf\VendraProduct\Database\Factories\ProductCategoryFactory;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Pages\CreateProductCategory;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Pages\EditProductCategory;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Pages\ListProductCategories;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Pages\ViewProductCategory;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\CreateProduct;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\ListProducts;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentSuperAdminTestContext();

    Filament::getPanel('admin')->plugin(
        SpatieTranslatablePlugin::make()->defaultLocales(['en', 'de']),
    );
});

it('renders the create product page under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    livewire(CreateProduct::class)
        ->assertOk();
});

it('renders the create product category page under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    livewire(CreateProductCategory::class)
        ->assertOk();
});

it('validates the product category form without failing to evaluate the attribute values rule', function (): void {
    livewire(CreateProductCategory::class)
        ->fillForm([
            'name'        => 'Test Category',
            'slug'        => 'test-category',
            'description' => 'A description for the category.',
            'active'      => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

it('renders the edit product category page under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    $category = ProductCategoryFactory::new()->createOne();

    livewire(EditProductCategory::class, ['record' => $category->getKey()])
        ->assertOk();
});

it('renders the view product category page under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    $category = ProductCategoryFactory::new()->createOne();

    livewire(ViewProductCategory::class, ['record' => $category->getKey()])
        ->assertOk();
});

it('renders the reorderable products table under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    $product = ProductFactory::new()->createOne();

    livewire(ListProducts::class)
        ->assertOk()
        ->call('loadTable')
        ->assertCanSeeTableRecords([$product]);
});

it('renders the reorderable product categories table under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    $productCategory = ProductCategoryFactory::new()->createOne();

    livewire(ListProductCategories::class)
        ->assertOk()
        ->call('loadTable')
        ->assertCanSeeTableRecords([$productCategory]);
});
