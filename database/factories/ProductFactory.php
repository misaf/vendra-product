<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraTenant\Models\Tenant;

/**
 * @extends Factory<Product>
 */
final class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'tenant_id'           => Tenant::factory(),
            'product_category_id' => fn(array $attributes) => ProductCategory::factory()->forTenant($attributes['tenant_id']),
            'name'                => ['en' => fake()->sentences(1, true)],
            'description'         => ['en' => fake()->realTextBetween(100, 200)],
            'slug'                => ['en' => fn(array $attributes) => Str::slug($attributes['name']['en'])],
            'quantity'            => fake()->numberBetween(1, 10),
            'stock_threshold'     => fake()->randomElement([null, 10, 20]),
            'in_stock'            => fake()->boolean(90),
            'available_soon'      => fake()->boolean(10),
            'availability_date'   => fake()->dateTimeBetween(Carbon::now(), Carbon::now()->addDays(30)),
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn(): array => ['tenant_id' => $tenant->id]);
    }

    public function forCategory(ProductCategory $productCategory): static
    {
        return $this->state(fn(): array => [
            'tenant_id'           => $productCategory->tenant_id,
            'product_category_id' => $productCategory->id,
        ]);
    }
}
