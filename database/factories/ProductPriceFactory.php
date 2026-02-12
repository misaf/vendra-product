<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Misaf\VendraCurrency\Models\Currency;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;

/**
 * @extends Factory<ProductPrice>
 */
final class ProductPriceFactory extends Factory
{
    protected $model = ProductPrice::class;

    public function definition(): array
    {
        return [
            'product_id'    => Product::factory(),
            'currency_code' => Currency::query()
                ->where('status', true)
                ->inRandomOrder()
                ->value('iso_code'),
            'price' => fake()->randomElement([9900, 14900, 19900, 24900, 49900, 99900]),
        ];
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn() => ['product_id' => $product->id]);
    }

    public function forCurrency(Currency $currency): static
    {
        return $this->state(fn() => ['currency_code' => $currency->iso_code]);
    }
}
