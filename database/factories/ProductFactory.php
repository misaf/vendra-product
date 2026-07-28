<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

/**
 * @extends Factory<Product>
 */
#[UseModel(Product::class)]
final class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_category_id' => ProductCategory::factory(),
            'name'                => ['en' => fake()->sentences(1, true)],
            'description'         => ['en' => fake()->realTextBetween(100, 200)],
            'quantity'            => fake()->numberBetween(1, 10),
            'stock_threshold'     => fake()->randomElement([null, 10, 20]),
            'in_stock'            => fake()->boolean(90),
            'available_soon'      => fake()->boolean(10),
            'availability_date'   => fake()->dateTimeBetween(Carbon::now(), Carbon::now()->addDays(30)),
        ];
    }

    /**
     * No-op without a tenant provider, since there is no `tenant_id` column.
     */
    public function forTenant(Model|int $tenant): static
    {
        if ( ! TenantAwareness::enabled()) {
            return $this;
        }

        return $this->state(fn(): array => [
            'tenant_id' => $tenant instanceof Model ? $tenant->getKey() : $tenant,
        ]);
    }

    public function forCategory(ProductCategory $productCategory): static
    {
        return $this->state(fn(): array => [
            'product_category_id' => $productCategory->id,
        ]);
    }
}
