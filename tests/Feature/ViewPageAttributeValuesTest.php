<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use Misaf\VendraProduct\Database\Factories\ProductCategoryFactory;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Pages\ViewProductCategory;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\ViewProduct;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraSupport\Capabilities\AttributeIntegration;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentAdminTestContext();

    Filament::getPanel('admin')->plugin(
        SpatieTranslatablePlugin::make()->defaultLocales(['en', 'de']),
    );
});

/**
 * Creates an attribute value on the category without importing the optional
 * attribute package, keeping this module decoupled from its provider.
 */
function createCategoryAttributeValueForViewTest(ProductCategory $productCategory): mixed
{
    $attributeId = DB::table('attributes')->insertGetId([
        'name'       => 'Weight',
        'position'   => 1,
        'active'     => true,
        'tenant_id'  => currentTestTenant()?->getKey(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $productCategory->attributeValues()->create([
        'attribute_id' => $attributeId,
        'value'        => '42',
    ]);
}

it('renders the product category view page including attribute values', function (): void {
    $productCategory = ProductCategoryFactory::new()->create();

    createCategoryAttributeValueForViewTest($productCategory);

    livewire(ViewProductCategory::class, ['record' => $productCategory->getKey()])
        ->assertOk()
        ->assertSee('Weight')
        ->assertSee('42');
})->skip(fn(): bool => ! AttributeIntegration::isAvailable(), 'vendra-attribute is not installed');

it('renders the product view page including selected attribute values', function (): void {
    $productCategory = ProductCategoryFactory::new()->create();
    $product = ProductFactory::new()->forCategory($productCategory)->create();

    $attributeValue = createCategoryAttributeValueForViewTest($productCategory);

    $product->selectedAttributeValues()->attach($attributeValue->getKey());

    livewire(ViewProduct::class, ['record' => $product->getKey()])
        ->assertOk()
        ->assertSee('Weight')
        ->assertSee('42');
})->skip(fn(): bool => ! AttributeIntegration::isAvailable(), 'vendra-attribute is not installed');
