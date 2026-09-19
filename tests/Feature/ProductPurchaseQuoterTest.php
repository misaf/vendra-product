<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Misaf\VendraProduct\Data\ProductPurchaseQuote;
use Misaf\VendraProduct\Data\ProductPurchaseRequest;
use Misaf\VendraProduct\Database\Factories\ProductCategoryFactory;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Database\Factories\ProductPriceFactory;
use Misaf\VendraProduct\Enums\ProductPurchaseRefusalEnum;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Services\ProductPurchaseQuoter;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

function purchasableProduct(int $quantity = 10, bool $activeCategory = true, ?int $usdPrice = 4800): Product
{
    $category = $activeCategory
        ? ProductCategoryFactory::new()->active()->createOne()
        : ProductCategoryFactory::new()->inactive()->createOne();

    $product = ProductFactory::new()->forCategory($category)->createOne([
        'in_stock' => true,
        'quantity' => $quantity,
    ]);

    ProductPriceFactory::new()->forProduct($product)->createOne(['currency_code' => 'EUR', 'price' => 9900]);

    if ($usdPrice !== null) {
        ProductPriceFactory::new()->forProduct($product)->createOne(['currency_code' => 'USD', 'price' => $usdPrice]);
    }

    return $product;
}

it('quotes each request in the currency and keeps the request keys', function (): void {
    $first = purchasableProduct(usdPrice: 4800);
    $second = purchasableProduct(usdPrice: 1250);

    $quotes = resolve(ProductPurchaseQuoter::class)->quote([
        'a' => new ProductPurchaseRequest($first->id, 2),
        'b' => new ProductPurchaseRequest($second->id, 1),
        'c' => new ProductPurchaseRequest($first->id, 1),
    ], 'USD');

    expect(array_keys($quotes))->toBe(['a', 'b', 'c'])
        ->and(Arr::get($quotes, 'a'))->toBeInstanceOf(ProductPurchaseQuote::class)
        ->and(Arr::get($quotes, 'a')->product->is($first))->toBeTrue()
        ->and(Arr::get($quotes, 'a')->unitAmount)->toBe(4800)
        ->and(Arr::get($quotes, 'b')->unitAmount)->toBe(1250)
        ->and(Arr::get($quotes, 'c')->unitAmount)->toBe(4800);
});

it('refuses a product that cannot be bought', function (Closure $product, int $quantity, ProductPurchaseRefusalEnum $refusal): void {
    $quotes = resolve(ProductPurchaseQuoter::class)->quote([new ProductPurchaseRequest($product()->id, $quantity)], 'USD');

    expect(Arr::get($quotes, 0))->toBe($refusal);
})->with([
    'inactive category' => [fn (): Product => purchasableProduct(activeCategory: false), 1, ProductPurchaseRefusalEnum::Unavailable],
    'deleted product' => [fn (): Product => tap(purchasableProduct())->delete(), 1, ProductPurchaseRefusalEnum::Unavailable],
    'not enough stock' => [fn (): Product => purchasableProduct(quantity: 1), 2, ProductPurchaseRefusalEnum::OutOfStock],
    'marked out of stock' => [fn (): Product => tap(purchasableProduct())->update(['in_stock' => false]), 1, ProductPurchaseRefusalEnum::OutOfStock],
    'no price in the currency' => [fn (): Product => purchasableProduct(usdPrice: null), 1, ProductPurchaseRefusalEnum::PriceMissing],
]);

it('loads every requested product with a fixed number of queries', function (): void {
    $requests = collect(range(1, 5))
        ->map(fn (): ProductPurchaseRequest => new ProductPurchaseRequest(purchasableProduct()->id, 1))
        ->all();

    DB::enableQueryLog();

    resolve(ProductPurchaseQuoter::class)->quote($requests, 'USD');

    expect(DB::getQueryLog())->toHaveCount(2);
});
