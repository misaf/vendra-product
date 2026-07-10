<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;
use Misaf\VendraSupport\Support\CurrencyIntegration;

/**
 * @extends Factory<ProductPrice>
 */
#[UseModel(ProductPrice::class)]
final class ProductPriceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id'    => Product::factory(),
            'currency_code' => fake()->randomElement(array_keys(CurrencyIntegration::options())),
            'price'         => fake()->randomElement([9900, 14900, 19900, 24900, 49900, 99900]),
        ];
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn() => ['product_id' => $product->id]);
    }

    public function forCurrencyCode(string $currencyCode): static
    {
        return $this->state(fn() => ['currency_code' => $currencyCode]);
    }

    /**
     * @param  object{iso_code: string}  $currency
     */
    public function forCurrency(object $currency): static
    {
        return $this->forCurrencyCode($currency->iso_code);
    }
}
