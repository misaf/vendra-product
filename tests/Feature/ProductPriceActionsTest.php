<?php

declare(strict_types=1);

use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\PanelRegistry;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use Misaf\VendraProduct\Database\Factories\ProductCategoryFactory;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Database\Factories\ProductPriceFactory;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\ListProducts;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentAdminTestContext();
    resolve(PanelRegistry::class)->getDefault()->plugin(SpatieTranslatablePlugin::make());
    Filament::bootCurrentPanel();
});

function pricedTableProduct(): Product
{
    return ProductFactory::new()->forCategory(ProductCategoryFactory::new()->createOne())->createOne();
}

it('rejects a negative price in the bulk set price action', function (): void {
    $product = pricedTableProduct();

    livewire(ListProducts::class)
        ->selectTableRecords([$product->getKey()])
        ->callAction(TestAction::make('setPrice')->table()->bulk(), [
            'currency_code' => ProductPrice::defaultCurrencyCode(),
            'price' => -100,
        ])
        ->assertHasFormErrors(['price' => 'min']);

    expect($product->productPrices()->count())->toBe(0);
});

it('rejects an unsupported currency in the bulk set price action', function (): void {
    $product = pricedTableProduct();

    livewire(ListProducts::class)
        ->selectTableRecords([$product->getKey()])
        ->callAction(TestAction::make('setPrice')->table()->bulk(), [
            'currency_code' => 'ZZZ',
            'price' => 1500,
        ])
        ->assertHasFormErrors(['currency_code']);

    expect($product->productPrices()->count())->toBe(0);
});

it('rejects a percentage that would push a price below zero', function (): void {
    $product = pricedTableProduct();
    ProductPriceFactory::new()->forProduct($product)->createOne([
        'currency_code' => ProductPrice::defaultCurrencyCode(),
        'price' => 1000,
    ]);

    livewire(ListProducts::class)
        ->selectTableRecords([$product->getKey()])
        ->callAction(TestAction::make('setPriceByPercentage')->table()->bulk(), [
            'percent' => -150,
        ])
        ->assertHasFormErrors(['percent' => 'min']);

    expect($product->productPrices()->count())->toBe(1);
});
