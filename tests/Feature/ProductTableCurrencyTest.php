<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filament\PanelRegistry;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use Misaf\VendraPermission\Tests\Support\PermissionModuleTestContext;
use Misaf\VendraProduct\Database\Factories\ProductCategoryFactory;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Database\Factories\ProductPriceFactory;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\ListProducts;

use function Pest\Livewire\livewire;

it('renders legacy prices with unsupported currency codes', function (): void {
    PermissionModuleTestContext::setUpFilamentAdminContext();
    app(PanelRegistry::class)->getDefault()->plugin(SpatieTranslatablePlugin::make());
    Filament::bootCurrentPanel();

    $category = ProductCategoryFactory::new()->createOne();
    $product = ProductFactory::new()->forCategory($category)->createOne();

    ProductPriceFactory::new()
        ->forProduct($product)
        ->forCurrencyCode('KS')
        ->create(['price' => 19_900]);

    livewire(ListProducts::class)
        ->call('loadTable')
        ->assertSee('19,900 KS');
});
