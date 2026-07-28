<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Schema;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use Misaf\VendraProduct\Database\Factories\ProductCategoryFactory;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\CreateProduct;
use Misaf\VendraProduct\Models\ProductPrice;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentSuperAdminTestContext();

    Filament::getPanel('admin')->plugin(
        SpatieTranslatablePlugin::make()->defaultLocales(['en', 'de']),
    );
});

it('requires a product quantity', function (): void {
    $productCategory = ProductCategoryFactory::new()->create();

    livewire(CreateProduct::class)
        ->fillForm([
            'product_category_id' => $productCategory->getKey(),
            'name'                => 'Product without quantity',
            'slug'                => 'product-without-quantity',
            'description'         => 'A product that must have a quantity.',
            'currency_code'       => ProductPrice::defaultCurrencyCode(),
            'price'               => 100,
            'quantity'            => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['quantity' => 'required'])
        ->assertNotNotified();
});

it('does not allow a null product quantity in the database', function (): void {
    $quantityColumn = collect(Schema::getColumns('products'))
        ->firstWhere('name', 'quantity');

    expect($quantityColumn)
        ->not->toBeNull()
        ->and($quantityColumn['nullable'])->toBeFalse();
});
