<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Database\Seeders;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Misaf\VendraProduct\Database\Factories\ProductCategoryFactory;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Database\Factories\ProductPriceFactory;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraProduct\Models\ProductPrice;
use Misaf\VendraSupport\Tenancy\Database\Seeders\DemoContentSeeder as BaseDemoContentSeeder;

final class DemoContentSeeder extends BaseDemoContentSeeder
{
    protected function seedFactories(): void
    {
        ProductCategoryFactory::new()
            ->active()
            ->count(4)
            ->create()
            ->each(fn (ProductCategory $productCategory): mixed => ProductFactory::new()
                ->forCategory($productCategory)
                ->count(2)
                ->create()
                ->each(fn (Product $product): array => array_map(
                    fn (string $currencyCode): ProductPrice => ProductPriceFactory::new()
                        ->forProduct($product)
                        ->forCurrencyCode($currencyCode)
                        ->createOne(),
                    array_keys(ProductPrice::currencyOptions()),
                )));
    }

    /**
     * Fixtures are keyed on the translated slug of the record's first locale
     * (the generated `token` is not reproducible), so a repeated run of the
     * same fixture file updates nothing and inserts nothing. Store
     * provisioning retries the whole seed list on failure, so a partial run
     * has to be safe to repeat.
     *
     * @param  list<array<string, mixed>>  $records
     */
    protected function seedFixtures(array $records): void
    {
        foreach ($records as $record) {
            $this->handleSeedFixtureRecord($this->validatedFixtureRecord($record));
        }
    }

    /**
     * @param array{
     *     name: non-empty-array<string, string>,
     *     description: non-empty-array<string, string>,
     *     slug: non-empty-array<string, string>,
     *     active: bool,
     *     products: list<array{
     *         name: non-empty-array<string, string>,
     *         description: non-empty-array<string, string>,
     *         slug: non-empty-array<string, string>,
     *         quantity: int,
     *         in_stock: bool,
     *         available_soon: bool,
     *         productPrices: list<array{currency_code: string, price: int|float}>
     *     }>
     * } $data
     */
    private function handleSeedFixtureRecord(array $data): void
    {
        $slug = Arr::get($data, 'slug');
        $locale = array_key_first($slug);

        $productCategory = ProductCategory::query()
            ->where('slug->'.$locale, $slug[$locale])
            ->first()
            ?? ProductCategory::query()->create([
                'name' => Arr::get($data, 'name'),
                'description' => Arr::get($data, 'description'),
                'slug' => Arr::get($data, 'slug'),
                'active' => Arr::get($data, 'active'),
            ]);

        foreach (Arr::get($data, 'products') as $productRecord) {
            $this->handleProductFixtureRecord($productCategory, $productRecord);
        }
    }

    /**
     * @param array{
     *     name: non-empty-array<string, string>,
     *     description: non-empty-array<string, string>,
     *     slug: non-empty-array<string, string>,
     *     quantity: int,
     *     in_stock: bool,
     *     available_soon: bool,
     *     productPrices: list<array{currency_code: string, price: int|float}>
     * } $productRecord
     */
    private function handleProductFixtureRecord(ProductCategory $productCategory, array $productRecord): void
    {
        $slug = Arr::get($productRecord, 'slug');
        $locale = array_key_first($slug);

        $product = $productCategory->products()
            ->where('slug->'.$locale, $slug[$locale])
            ->first()
            ?? $productCategory->products()->create([
                'name' => Arr::get($productRecord, 'name'),
                'description' => Arr::get($productRecord, 'description'),
                'slug' => Arr::get($productRecord, 'slug'),
                'quantity' => Arr::get($productRecord, 'quantity'),
                'in_stock' => Arr::get($productRecord, 'in_stock'),
                'available_soon' => Arr::get($productRecord, 'available_soon'),
            ]);

        foreach (Arr::get($productRecord, 'productPrices') as $productPriceRecord) {
            $this->handleProductPriceFixtureRecord($product, $productPriceRecord);
        }
    }

    /**
     * A product carries at most one price per currency, so the currency code is
     * the natural key within the product.
     *
     * @param  array{currency_code: string, price: int|float}  $productPriceRecord
     */
    private function handleProductPriceFixtureRecord(Product $product, array $productPriceRecord): void
    {
        $product->productPrices()->firstOrCreate(
            ['currency_code' => Arr::get($productPriceRecord, 'currency_code')],
            ['price' => Arr::get($productPriceRecord, 'price')],
        );
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array{
     *     name: non-empty-array<string, string>,
     *     description: non-empty-array<string, string>,
     *     slug: non-empty-array<string, string>,
     *     active: bool,
     *     products: list<array{
     *         name: non-empty-array<string, string>,
     *         description: non-empty-array<string, string>,
     *         slug: non-empty-array<string, string>,
     *         quantity: int,
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
         *     active: bool,
         *     products: list<array{
         *         name: non-empty-array<string, string>,
         *         description: non-empty-array<string, string>,
         *         slug: non-empty-array<string, string>,
         *         quantity: int,
         *         in_stock: bool,
         *         available_soon: bool,
         *         productPrices: list<array{currency_code: string, price: int|float}>
         *     }>
         * } $validated
         */
        $validated = Validator::make(
            data: $record,
            rules: [
                'name' => ['required', 'array', 'min:1'],
                'name.*' => ['required', 'string'],
                'description' => ['required', 'array', 'min:1'],
                'description.*' => ['required', 'string'],
                'slug' => ['required', 'array', 'min:1'],
                'slug.*' => ['required', 'string'],
                'active' => ['required', 'boolean'],
                'products' => ['required', 'array', 'list'],
                'products.*' => ['required', 'array:name,description,slug,quantity,in_stock,available_soon,productPrices'],
                'products.*.name' => ['required', 'array', 'min:1'],
                'products.*.name.*' => ['required', 'string'],
                'products.*.description' => ['required', 'array', 'min:1'],
                'products.*.description.*' => ['required', 'string'],
                'products.*.slug' => ['required', 'array', 'min:1'],
                'products.*.slug.*' => ['required', 'string'],
                'products.*.quantity' => ['required', 'integer', 'min:0'],
                'products.*.in_stock' => ['required', 'boolean'],
                'products.*.available_soon' => ['required', 'boolean'],
                'products.*.productPrices' => ['required', 'array', 'list'],
                'products.*.productPrices.*' => ['required', 'array:currency_code,price'],
                'products.*.productPrices.*.currency_code' => ['required', 'string', 'alpha:ascii', 'size:3'],
                'products.*.productPrices.*.price' => ['required', 'numeric'],
            ],
        )->validate();

        return $validated;
    }
}
