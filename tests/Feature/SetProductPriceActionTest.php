<?php

declare(strict_types=1);

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
