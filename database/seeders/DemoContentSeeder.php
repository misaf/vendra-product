<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Database\Seeders;

use Illuminate\Support\Facades\Validator;
use Misaf\VendraProduct\Database\Factories\ProductCategoryFactory;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Database\Factories\ProductPriceFactory;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraProduct\Models\ProductPrice;
use Misaf\VendraSupport\Database\Seeders\DemoContentSeeder as BaseDemoContentSeeder;
use Misaf\VendraSupport\Support\CurrencyIntegration;

final class DemoContentSeeder extends BaseDemoContentSeeder
{
    protected function seedFactories(): void
    {
        $this->currentTenantOrNull();

        ProductCategoryFactory::new()
            ->enabled()
            ->count(4)
            ->create()
            ->each(fn(ProductCategory $productCategory): mixed => ProductFactory::new()
                ->forCategory($productCategory)
                ->count(2)
                ->create()
                ->each(fn(Product $product): array => array_map(
                    fn(string $currencyCode): ProductPrice => ProductPriceFactory::new()
                        ->forProduct($product)
                        ->forCurrencyCode($currencyCode)
                        ->create(),
                    CurrencyIntegration::activeCurrencyCodes(),
                )));
    }

    /**
     * @param  list<array<string, mixed>>  $records
     */
    protected function seedFixtures(array $records): void
    {
        $this->currentTenantOrNull();

        foreach ($records as $record) {
            $this->handleSeedFixtureRecord($this->validatedFixtureRecord($record));
        }
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
    private function handleSeedFixtureRecord(array $data): void
    {
        $productCategory = ProductCategory::create([
            'name'        => $data['name'],
            'description' => $data['description'],
            'slug'        => $data['slug'],
            'status'      => $data['status'],
        ]);

        foreach ($data['products'] as $productRecord) {
            $this->handleProductFixtureRecord($productCategory, $productRecord);
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
    private function handleProductFixtureRecord(ProductCategory $productCategory, array $productRecord): void
    {
        $product = $productCategory->products()->create([
            'name'           => $productRecord['name'],
            'description'    => $productRecord['description'],
            'slug'           => $productRecord['slug'],
            'in_stock'       => $productRecord['in_stock'],
            'available_soon' => $productRecord['available_soon'],
        ]);

        $product->productPrices()->createMany($productRecord['productPrices']);
    }

    /**
     * @param  array<string, mixed>  $record
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
