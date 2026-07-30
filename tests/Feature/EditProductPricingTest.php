<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\EditProduct;
use Misaf\VendraProduct\Models\ProductPrice;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentAdminTestContext();

    Filament::getPanel('admin')->plugin(
        SpatieTranslatablePlugin::make()->defaultLocales(['en', 'de']),
    );
});

it('remains editable for products that do not have a price row yet', function (): void {
    $product = ProductFactory::new()->create();

    livewire(EditProduct::class, ['record' => $product->getKey()])
        ->assertOk()
        ->fillForm([
            'currency_code' => ProductPrice::defaultCurrencyCode(),
            'price'         => 1500,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->productPrices()->count())->toBe(1);
});

it('reuses the existing price row when pricing is unchanged', function (): void {
    $product = ProductFactory::new()->create();

    $product->productPrices()->create([
        'currency_code' => ProductPrice::defaultCurrencyCode(),
        'price'         => 1500,
    ]);

    livewire(EditProduct::class, ['record' => $product->getKey()])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->productPrices()->count())->toBe(1);
});
