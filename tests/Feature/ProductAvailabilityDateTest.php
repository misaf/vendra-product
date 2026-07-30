<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use Misaf\VendraProduct\Database\Factories\ProductCategoryFactory;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\CreateProduct;
use Misaf\VendraProduct\Models\ProductPrice;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentAdminTestContext();

    Filament::getPanel('admin')->plugin(
        SpatieTranslatablePlugin::make()->defaultLocales(['en', 'de']),
    );
});

it('rejects availability dates in the past for products available soon', function (): void {
    $productCategory = ProductCategoryFactory::new()->create();

    livewire(CreateProduct::class)
        ->fillForm([
            'product_category_id' => $productCategory->getKey(),
            'name'                => 'Upcoming product',
            'slug'                => 'upcoming-product',
            'description'         => 'A product that arrives later.',
            'currency_code'       => ProductPrice::defaultCurrencyCode(),
            'price'               => 100,
            'available_soon'      => true,
            'availability_date'   => now()->subDay()->format('Y-m-d H:i:s'),
        ])
        ->call('create')
        ->assertHasFormErrors(['availability_date']);
});
