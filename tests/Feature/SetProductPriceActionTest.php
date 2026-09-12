<?php

declare(strict_types=1);

use Illuminate\Validation\ValidationException;
use Misaf\VendraProduct\Actions\SetProductPriceAction;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Models\ProductPrice;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('records a price row in minor units with an uppercased currency', function (): void {
    $product = ProductFactory::new()->create();
    $currency = ProductPrice::defaultCurrencyCode();

    $price = resolve(SetProductPriceAction::class)->execute($product, strtolower($currency), 1500);

    expect($price->currency_code)->toBe($currency)
        ->and((int) $price->price->getAmount())->toBe(1500)
        ->and($product->productPrices()->count())->toBe(1);
});

it('rejects an unsupported currency', function (): void {
    $product = ProductFactory::new()->create();

    expect(fn () => resolve(SetProductPriceAction::class)->execute($product, 'ZZZ', 1500))
        ->toThrow(ValidationException::class);

    expect($product->productPrices()->count())->toBe(0);
});

it('rejects a negative price', function (): void {
    $product = ProductFactory::new()->create();

    expect(fn () => resolve(SetProductPriceAction::class)->execute($product, ProductPrice::defaultCurrencyCode(), -100))
        ->toThrow(ValidationException::class);

    expect($product->productPrices()->count())->toBe(0);
});
