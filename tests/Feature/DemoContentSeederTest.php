<?php

declare(strict_types=1);

use Misaf\VendraProduct\Database\Seeders\DemoContentSeeder;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraProduct\Models\ProductPrice;

it('seeds its demo fixtures again without duplicating rows', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    makeCurrentTestTenant();

    resolve(DemoContentSeeder::class)->run();

    $productCategories = ProductCategory::query()->count();
    $products = Product::query()->count();
    $productPrices = ProductPrice::query()->count();

    expect($productCategories)->toBeGreaterThan(0)
        ->and($products)->toBeGreaterThan(0)
        ->and($productPrices)->toBeGreaterThan(0);

    resolve(DemoContentSeeder::class)->run();

    expect(ProductCategory::query()->count())->toBe($productCategories)
        ->and(Product::query()->count())->toBe($products)
        ->and(ProductPrice::query()->count())->toBe($productPrices);
});

it('stocks each product at the quantity its fixture declares', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    makeCurrentTestTenant();

    resolve(DemoContentSeeder::class)->run();

    expect(Product::query()->where('slug->en', 'dell-xps-13')->sole()->quantity)->toBe(12)
        ->and(Product::query()->where('slug->en', 'apple-imac-24')->sole()->quantity)->toBe(0);
});
