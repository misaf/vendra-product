<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Database\Seeders;

use Illuminate\Support\Facades\Validator;
use Misaf\VendraCurrency\Models\Currency;
use Misaf\VendraProduct\Database\Factories\ProductCategoryFactory;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Database\Factories\ProductPriceFactory;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraSupport\Database\Seeders\DemoContentSeeder as BaseDemoContentSeeder;
use Misaf\VendraTenant\Concerns\RequiresCurrentTenant;
use Misaf\VendraTenant\Models\Tenant;

final class DemoContentSeeder extends BaseDemoContentSeeder
{
    use RequiresCurrentTenant;

    protected function seedFactories(): void
    {
        $tenant = $this->currentTenant();

        $this->seedFactoryRecords($tenant);
    }

    /**
     * @param list<array<string, mixed>> $records
     */
    protected function seedFixtures(array $records): void
    {
        $tenant = $this->currentTenant();

        foreach ($records as $record) {
            $this->seedFixtureRecord($tenant, $record);
        }
    }

    protected function seedFactoryRecords(Tenant $tenant): void
    {
        ProductCategoryFactory::new()
            ->forTenant($tenant)
            ->enabled()
            ->count(4)
            ->create()
            ->each(fn(ProductCategory $productCategory): mixed => ProductFactory::new()
                ->forCategory($productCategory)
                ->count(2)
                ->create()
                ->each(fn($product): mixed => Currency::query()
                    ->where('tenant_id', $tenant->id)
                    ->where('status', true)
                    ->get()
                    ->each(fn(Currency $currency): mixed => ProductPriceFactory::new()
                        ->forProduct($product)
                        ->forCurrency($currency)
                        ->create())));
    }

    protected function seedFixtureRecord(Tenant $tenant, array $record): void
    {
        $data = $this->validatedFixtureRecord($record);

        $this->handleSeedFixtureRecord($tenant, $data);
    }

    /**
     * @param array{
     *     name: non-empty-array<string, string>,
     *     description: non-empty-array<string, string>,
     *     slug: non-empty-array<string, string>,
     *     status: bool,
     *     products: list<array{
     *         name: non-empty-array<string, string>,
     *         description: non-empty-array<string, string>,
     *         slug: non-empty-array<string, string>,
     *         in_stock: bool,
     *         available_soon: bool,
     *         productPrices: list<array{currency_code: string, price: int|float}>
     *     }>
     * } $data
     */
    private function handleSeedFixtureRecord(Tenant $tenant, array $data): void
    {
        $productCategory = new ProductCategory([
            'name'        => $data['name'],
            'description' => $data['description'],
            'slug'        => $data['slug'],
            'status'      => $data['status'],
        ]);

        $productCategory->tenant_id = $tenant->id;
        $productCategory->save();

        foreach ($data['products'] as $productRecord) {
            $this->handleProductFixtureRecord($tenant, $productCategory, $productRecord);
        }
    }

    /**
     * @param array{
     *     name: non-empty-array<string, string>,
     *     description: non-empty-array<string, string>,
     *     slug: non-empty-array<string, string>,
     *     in_stock: bool,
     *     available_soon: bool,
     *     productPrices: list<array{currency_code: string, price: int|float}>
     * } $productRecord
     */
    private function handleProductFixtureRecord(Tenant $tenant, ProductCategory $productCategory, array $productRecord): void
    {
        $product = $productCategory->products()->make([
            'name'           => $productRecord['name'],
            'description'    => $productRecord['description'],
            'slug'           => $productRecord['slug'],
            'in_stock'       => $productRecord['in_stock'],
            'available_soon' => $productRecord['available_soon'],
        ]);

        $product->tenant_id = $tenant->id;
        $product->save();

        $product->productPrices()->createMany($productRecord['productPrices']);
    }

    /**
     * @param array<string, mixed> $record
     *
     * @return array{
     *     name: non-empty-array<string, string>,
     *     description: non-empty-array<string, string>,
     *     slug: non-empty-array<string, string>,
     *     status: bool,
     *     products: list<array{
     *         name: non-empty-array<string, string>,
     *         description: non-empty-array<string, string>,
     *         slug: non-empty-array<string, string>,
     *         in_stock: bool,
     *         available_soon: bool,
     *         productPrices: list<array{currency_code: string, price: int|float}>
     *     }>
     * }
     */
    private function validatedFixtureRecord(array $record): array
    {
        /** @var array{
         *     name: non-empty-array<string, string>,
         *     description: non-empty-array<string, string>,
         *     slug: non-empty-array<string, string>,
         *     status: bool,
         *     products: list<array{
         *         name: non-empty-array<string, string>,
         *         description: non-empty-array<string, string>,
         *         slug: non-empty-array<string, string>,
         *         in_stock: bool,
         *         available_soon: bool,
         *         productPrices: list<array{currency_code: string, price: int|float}>
         *     }>
         * } $validated
         */
        $validated = Validator::make(
            data: $record,
            rules: [
                'name'                                     => ['required', 'array', 'min:1'],
                'name.*'                                   => ['required', 'string'],
                'description'                              => ['required', 'array', 'min:1'],
                'description.*'                            => ['required', 'string'],
                'slug'                                     => ['required', 'array', 'min:1'],
                'slug.*'                                   => ['required', 'string'],
                'status'                                   => ['required', 'boolean'],
                'products'                                 => ['required', 'array', 'list'],
                'products.*'                               => ['required', 'array:name,description,slug,in_stock,available_soon,productPrices'],
                'products.*.name'                          => ['required', 'array', 'min:1'],
                'products.*.name.*'                        => ['required', 'string'],
                'products.*.description'                   => ['required', 'array', 'min:1'],
                'products.*.description.*'                 => ['required', 'string'],
                'products.*.slug'                          => ['required', 'array', 'min:1'],
                'products.*.slug.*'                        => ['required', 'string'],
                'products.*.in_stock'                      => ['required', 'boolean'],
                'products.*.available_soon'                => ['required', 'boolean'],
                'products.*.productPrices'                 => ['required', 'array', 'list'],
                'products.*.productPrices.*'               => ['required', 'array:currency_code,price'],
                'products.*.productPrices.*.currency_code' => ['required', 'string'],
                'products.*.productPrices.*.price'         => ['required', 'numeric'],
            ],
        )->validate();

        return $validated;
    }

}
