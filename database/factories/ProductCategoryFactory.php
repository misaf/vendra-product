<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraTenant\Models\Tenant;

/**
 * @extends Factory<ProductCategory>
 */
final class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    public function definition(): array
    {
        return [
            'tenant_id'   => Tenant::factory(),
            'name'        => ['en' => fake()->sentences(1, true)],
            'description' => ['en' => fake()->realTextBetween(100, 200)],
            'slug'        => ['en' => fn(array $attributes) => Str::slug($attributes['name']['en'])],
            'status'      => fake()->boolean(80),
        ];
    }

    public function forTenant(Tenant|int $tenant): static
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        return $this->state(fn(): array => ['tenant_id' => $tenantId]);
    }

    public function enabled(): static
    {
        return $this->state(fn(): array => ['status' => true]);
    }

    public function disabled(): static
    {
        return $this->state(fn(): array => ['status' => false]);
    }
}
