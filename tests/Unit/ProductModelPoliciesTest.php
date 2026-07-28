<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\SoftDeletes;
use Misaf\VendraProduct\Enums\ProductCategoryPolicyEnum;
use Misaf\VendraProduct\Enums\ProductPolicyEnum;
use Misaf\VendraProduct\Enums\ProductPricePolicyEnum;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraProduct\Models\ProductPrice;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

it('applies shared tenant ownership and soft deletes to product models', function (): void {
    expect(class_uses_recursive(Product::class))->toContain(BelongsToTenant::class, SoftDeletes::class)
        ->and(class_uses_recursive(ProductCategory::class))->toContain(BelongsToTenant::class, SoftDeletes::class);
});

it('does not apply tenant ownership to the tenant-agnostic product price model', function (): void {
    expect(class_uses_recursive(ProductPrice::class))->not->toContain(BelongsToTenant::class);
});

it('defines translatable fields on product models', function (): void {
    expect((new Product())->translatable)->toBe(['name', 'description', 'slug'])
        ->and((new ProductCategory())->translatable)->toBe(['name', 'description', 'slug']);
});

it('hides the tenant association from product serialization', function (): void {
    expect((new Product())->getHidden())->toContain('tenant_id')
        ->and((new ProductCategory())->getHidden())->toContain('tenant_id');
});

it('defines policy permissions for the product resource', function (): void {
    $permissions = array_column(ProductPolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(12);
});

it('defines policy permissions for the product category resource', function (): void {
    $permissions = array_column(ProductCategoryPolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(11);
});

it('defines policy permissions for the product price resource', function (): void {
    $permissions = array_column(ProductPricePolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(10);
});

it('uses kebab-case permission names scoped per model', function (): void {
    $productPermissions = array_column(ProductPolicyEnum::cases(), 'value');
    $categoryPermissions = array_column(ProductCategoryPolicyEnum::cases(), 'value');
    $pricePermissions = array_column(ProductPricePolicyEnum::cases(), 'value');

    expect($productPermissions)->toHaveCount(count(array_unique($productPermissions)))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/');

    expect($categoryPermissions)->toHaveCount(count(array_unique($categoryPermissions)))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/');

    expect($pricePermissions)->toHaveCount(count(array_unique($pricePermissions)))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/');
});
