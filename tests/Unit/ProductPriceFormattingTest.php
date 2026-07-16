<?php

declare(strict_types=1);

use Misaf\VendraProduct\Models\ProductPrice;
use Misaf\VendraSupport\Contracts\CurrencyResolver;

it('filters unsupported currency options and falls back to the configured currency', function (): void {
    app()->instance(CurrencyResolver::class, new class () implements CurrencyResolver {
        public function available(): bool
        {
            return true;
        }

        public function defaultCode(): string
        {
            return 'KS';
        }

        public function options(): array
        {
            return [
                'KS'  => 'Invalid currency',
                'usd' => 'US Dollar',
            ];
        }

        public function activeCodes(): array
        {
            return ['KS', 'usd'];
        }
    });

    expect(ProductPrice::defaultCurrencyCode())->toBe('USD')
        ->and(ProductPrice::currencyOptions())->toBe(['USD' => 'US Dollar']);
});

it('formats legacy prices with unsupported currency codes without throwing', function (): void {
    $productPrice = new ProductPrice([
        'currency_code' => 'KS',
        'price'         => 19_900,
    ]);

    expect($productPrice->formattedPrice())->toBe('19,900 KS');
});
