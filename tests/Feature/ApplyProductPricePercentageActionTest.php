<?php

declare(strict_types=1);

use Misaf\VendraProduct\Actions\ApplyProductPricePercentageAction;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Models\ProductPrice;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('raises the latest price by a positive percent', function (): void {
    $product = ProductFactory::new()->create();

    $product->productPrices()->create([
        'currency_code' => ProductPrice::defaultCurrencyCode(),
        'price' => 1000,
    ]);

    $price = resolve(ApplyProductPricePercentageAction::class)->execute($product, 10.0);

    expect($price)->not->toBeNull()
        ->and((int) $price?->price->getAmount())->toBe(1100)
        ->and($product->productPrices()->count())->toBe(2);
});

it('lowers the latest price by a negative percent', function (): void {
    $product = ProductFactory::new()->create();

    $product->productPrices()->create([
        'currency_code' => ProductPrice::defaultCurrencyCode(),
        'price' => 1000,
    ]);

    $price = resolve(ApplyProductPricePercentageAction::class)->execute($product, -10.0);

    expect($price)->not->toBeNull()
        ->and((int) $price?->price->getAmount())->toBe(900);
});

it('returns null when the product has no price yet', function (): void {
    $product = ProductFactory::new()->create();

    expect(resolve(ApplyProductPricePercentageAction::class)->execute($product, 10.0))->toBeNull()
        ->and($product->productPrices()->count())->toBe(0);
});
