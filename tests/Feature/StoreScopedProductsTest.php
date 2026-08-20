<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use Misaf\VendraSupport\Tenancy\TenantSchema;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

/*
 | Products belong to a store, but the package must never say so: it owns its
 | rows through the neutral tenant foreign key and lets the installed provider
 | decide which model that points at. In Vendra the current tenant is a Store, so
 | "Store A's products" and "the current tenant's products" are the same set.
 */
it('owns products through the configured tenant foreign key', function (): void {
    expect(TenantSchema::column())->toBe('tenant_id')
        ->and(Schema::hasColumn('products', TenantSchema::column()))->toBeTrue()
        ->and(TenantSchema::hasTenantColumn('products'))->toBeTrue();
})->skip(fn(): bool => ! TenantAwareness::enabled(), 'tenancy is not enabled');

it('returns only the current store products', function (): void {
    $storeA = currentTestTenant();
    $storeB = createTestTenant();

    $first = ProductFactory::new()->create(['name' => ['en' => 'Store A rose', 'de' => 'Store A rose']]);

    switchToTestTenant($storeB);
    $second = ProductFactory::new()->create(['name' => ['en' => 'Store B tulip', 'de' => 'Store B tulip']]);

    expect(Product::query()->pluck('id')->all())->toBe([$second->getKey()]);

    switchToTestTenant($storeA);

    expect(Product::query()->pluck('id')->all())->toBe([$first->getKey()])
        ->and(Product::query()->withoutGlobalScopes()->count())->toBe(2)
        ->and($first->getAttribute(TenantSchema::column()))->toBe($storeA?->getKey())
        ->and($second->getAttribute(TenantSchema::column()))->toBe($storeB?->getKey());
})->skip(fn(): bool => ! TenantAwareness::enabled(), 'tenancy is not enabled');

it('reaches its owning store through the generic tenant relation', function (): void {
    $store = currentTestTenant();
    $product = ProductFactory::new()->create();

    /*
     | The package names the role, not the business model, so the relation stays
     | `tenant()`. In Vendra the current tenant IS the Store, so it resolves to
     | one without this package ever naming it.
     */
    expect($product->tenant()->getForeignKeyName())->toBe(TenantSchema::column())
        ->and($product->tenant->getKey())->toBe($store?->getKey())
        ->and($product->tenant)->toBeInstanceOf(app(TenantResolver::class)->modelClass());
})->skip(fn(): bool => ! TenantAwareness::enabled(), 'tenancy is not enabled');
