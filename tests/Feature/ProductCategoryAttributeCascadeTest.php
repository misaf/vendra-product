<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Misaf\VendraProduct\Database\Factories\ProductCategoryFactory;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraProduct\Observers\ProductCategoryObserver;
use Misaf\VendraSupport\Capabilities\AttributeIntegration;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

/**
 * Creates an attribute value on the category without importing the optional
 * attribute package, keeping this module decoupled from its provider.
 */
function createCategoryAttributeValueForCascadeTest(ProductCategory $productCategory): mixed
{
    $attributeId = DB::table('attributes')->insertGetId([
        'name'       => 'Material ' . fake()->unique()->word(),
        'position'   => 1,
        'active'     => true,
        'tenant_id'  => currentTestTenant()?->getKey(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $productCategory->attributeValues()->create([
        'attribute_id' => $attributeId,
        'value'        => fake()->unique()->word(),
    ]);
}

it('soft deletes the category attribute values alongside products and prices', function (): void {
    $productCategory = ProductCategoryFactory::new()->create();
    $attributeValue = createCategoryAttributeValueForCascadeTest($productCategory);

    $productCategory->delete();
    (new ProductCategoryObserver())->deleted($productCategory);

    expect($productCategory->attributeValues()->count())->toBe(0)
        ->and($productCategory->attributeValues()->withTrashed()->whereKey($attributeValue->getKey())->exists())->toBeTrue();
})->skip(fn(): bool => ! AttributeIntegration::isAvailable(), 'vendra-attribute is not installed');

it('hard deletes the category attribute values when the category is force deleted', function (): void {
    $productCategory = ProductCategoryFactory::new()->create();
    $attributeValue = createCategoryAttributeValueForCascadeTest($productCategory);

    $productCategory->forceDelete();

    expect(DB::table('attribute_values')->where('id', $attributeValue->getKey())->exists())->toBeFalse();
})->skip(fn(): bool => ! AttributeIntegration::isAvailable(), 'vendra-attribute is not installed');

it('detaches selections from the old category when a product moves to another category', function (): void {
    $oldCategory = ProductCategoryFactory::new()->create();
    $newCategory = ProductCategoryFactory::new()->create();
    $product = ProductFactory::new()->forCategory($oldCategory)->create();

    $attributeValue = createCategoryAttributeValueForCascadeTest($oldCategory);
    $product->selectedAttributeValues()->attach($attributeValue->getKey());

    $product->update(['product_category_id' => $newCategory->getKey()]);

    expect($product->selectedAttributeValues()->count())->toBe(0);
})->skip(fn(): bool => ! AttributeIntegration::isAvailable(), 'vendra-attribute is not installed');

it('detaches selections when a product is force deleted', function (): void {
    $productCategory = ProductCategoryFactory::new()->create();
    $product = ProductFactory::new()->forCategory($productCategory)->create();

    $attributeValue = createCategoryAttributeValueForCascadeTest($productCategory);
    $product->selectedAttributeValues()->attach($attributeValue->getKey());

    $product->forceDelete();

    expect(DB::table('attribute_value_selections')->where('attribute_value_id', $attributeValue->getKey())->exists())->toBeFalse();
})->skip(fn(): bool => ! AttributeIntegration::isAvailable(), 'vendra-attribute is not installed');

it('cleans up selections when a category is force deleted and its attribute values are destroyed', function (): void {
    $productCategory = ProductCategoryFactory::new()->create();
    $product = ProductFactory::new()->forCategory($productCategory)->create();

    $attributeValue = createCategoryAttributeValueForCascadeTest($productCategory);
    $product->selectedAttributeValues()->attach($attributeValue->getKey());

    $productCategory->forceDelete();

    expect(DB::table('attribute_value_selections')->where('attribute_value_id', $attributeValue->getKey())->exists())->toBeFalse();
})->skip(fn(): bool => ! AttributeIntegration::isAvailable(), 'vendra-attribute is not installed');
