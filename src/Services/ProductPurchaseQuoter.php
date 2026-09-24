<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Services;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Collection;
use Misaf\VendraProduct\Data\ProductPurchaseQuote;
use Misaf\VendraProduct\Data\ProductPurchaseRequest;
use Misaf\VendraProduct\Enums\ProductPurchaseRefusalEnum;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;

/**
 * A product can be bought when its category is active, it is in stock with
 * enough quantity for the request, and it has a price in the currency.
 * Prices are kept as history, so the newest one in the currency applies, as
 * `latestProductPrice` shows it.
 *
 * Stock is only checked, never reserved; the caller owns any locking.
 */
final readonly class ProductPurchaseQuoter
{
    /**
     * Quote every request from one catalog query, keeping the request keys.
     *
     * @template TKey of array-key
     *
     * @param  array<TKey, ProductPurchaseRequest>  $requests
     * @return array<TKey, ProductPurchaseQuote|ProductPurchaseRefusalEnum>
     */
    public function quote(array $requests, string $currencyCode): array
    {
        $products = $this->purchasableProducts($requests, $currencyCode);

        return array_map(
            fn (ProductPurchaseRequest $request): ProductPurchaseQuote|ProductPurchaseRefusalEnum => $this->quoteOne(
                $products->get($request->productId),
                $request->quantity,
            ),
            $requests,
        );
    }

    private function quoteOne(?Product $product, int $quantity): ProductPurchaseQuote|ProductPurchaseRefusalEnum
    {
        if (! $product instanceof Product) {
            return ProductPurchaseRefusalEnum::Unavailable;
        }

        if (! $product->in_stock || $product->quantity < $quantity) {
            return ProductPurchaseRefusalEnum::OutOfStock;
        }

        $price = $product->productPrices->first();

        if (! $price instanceof ProductPrice) {
            return ProductPurchaseRefusalEnum::PriceMissing;
        }

        return new ProductPurchaseQuote($product, (int) $price->price->getAmount());
    }

    /**
     * @param  array<array-key, ProductPurchaseRequest>  $requests
     * @return Collection<int, Product>
     */
    private function purchasableProducts(array $requests, string $currencyCode): Collection
    {
        $productIds = array_values(array_unique(array_map(
            static fn (ProductPurchaseRequest $request): int => $request->productId,
            $requests,
        )));

        if ($productIds === []) {
            return new Collection;
        }

        return Product::query()
            ->with(['productPrices' => fn (Builder $query) => $query->where('currency_code', $currencyCode)->latest('id')])
            ->whereHas('productCategory', fn (Builder $query) => $query->active())
            ->whereKey($productIds)
            ->get()
            ->keyBy('id');
    }
}
