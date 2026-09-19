# Vendra Product

Tenant-aware product management for Vendra applications.

## Features

- Product categories
- Products
- Product prices per currency
- Filament resources on the `admin` panel

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5
- Livewire 4
- Pest 4
- Tailwind CSS 4
- `misaf/vendra-support`
- `misaf/vendra-multimedia`

Optional:

- `misaf/vendra-attribute` — enables reusable product attributes and values through the shared support resolver
- `misaf/vendra-currency` — enables management of the active currencies used by pricing forms and demo seeders
- `misaf/vendra-tagger` — enables assigning `product`-typed tags through the shared support resolver

## Installation

```bash
composer require misaf/vendra-product
php artisan vendor:publish --tag=vendra-product-migrations
php artisan migrate
```

Optional configuration and translations:

```bash
php artisan vendor:publish --tag=vendra-product-config
php artisan vendor:publish --tag=vendra-product-translations
```

The service provider and Filament plugin are auto-registered.

## Usage

Create a category:

```php
use Misaf\VendraProduct\Models\ProductCategory;

$category = ProductCategory::query()->create([
    'name' => ['en' => 'Beverages'],
    'description' => ['en' => 'Cold and hot drinks'],
    'slug' => ['en' => 'beverages'],
    'active' => true,
]);
```

Create a product:

```php
use Misaf\VendraProduct\Models\Product;

$product = Product::query()->create([
    'product_category_id' => $category->id,
    'name' => ['en' => 'Orange Juice'],
    'description' => ['en' => 'Fresh and natural'],
    'slug' => ['en' => 'orange-juice'],
    'quantity' => 20,
    'stock_threshold' => 5,
    'in_stock' => true,
    'available_soon' => false,
]);
```

Add a price:

```php
use Misaf\VendraProduct\Models\ProductPrice;

ProductPrice::query()->create([
    'product_id' => $product->id,
    'currency_code' => 'USD',
    'price' => 9900,
]);
```

Load products with relationships:

```php
$products = Product::query()
    ->with(['productCategory', 'latestProductPrice'])
    ->get();
```

Quote purchases for a checkout:

```php
use Misaf\VendraProduct\Data\ProductPurchaseQuote;
use Misaf\VendraProduct\Data\ProductPurchaseRequest;
use Misaf\VendraProduct\Services\ProductPurchaseQuoter;

$quotes = app(ProductPurchaseQuoter::class)->quote([
    'line-1' => new ProductPurchaseRequest(productId: $product->id, quantity: 2),
], 'USD');

$quote = $quotes['line-1']; // ProductPurchaseQuote, or a ProductPurchaseRefusalEnum case
```

The quoter loads every requested product in one query and keeps the request
keys. A product can be bought when its category is active, it is in stock with
enough quantity, and it has a price in the currency; otherwise the result is
`Unavailable`, `OutOfStock`, or `PriceMissing`. Prices are history, so the
newest price in the currency applies, matching `latestProductPrice`. It checks stock but never
takes it, and knows nothing about carts or orders.

Take and return stock with `DeductProductStockAction` and
`RestockProductsAction`, both keyed by product id. Deduction locks the products
in id order, rechecks `in_stock` and the quantity under the lock, and takes
nothing when any product falls short, throwing
`InsufficientProductStockException` with that product's id. Taking a
product's last unit switches `in_stock` off. Restocking never switches it back
on, so the merchant re-enables the product. Restocking also returns stock to
deleted products.

Duplicate a product with `DuplicateProductAction`. It gives the copy a name
and slug no other product in the tenant uses, and copies its prices, media,
attribute selections, and tags in one transaction.

### Optional tags

Install `misaf/vendra-tagger` in the host application to enable the Tags tab and table column automatically. Product does not require or import Tagger or Spatie Tags; both packages communicate through the `TagResolver` contract in `misaf/vendra-support`.

Create tags with the reserved `product` type in the Tagger resource, then assign them from the product form:

```php
use Misaf\VendraTagger\Models\Tagger;

$tag = Tagger::findOrCreate('Featured', type: 'product', locale: 'en');

$product->tags()->sync([$tag->getKey()]);
```

Without Tagger, Product continues working without tag queries or tag UI.

## Filament

Resources are available in the shared `Catalog` cluster on the `admin` panel:

- Product Categories
- Products
- Product Prices

Demo seeders use bundled JSON fixtures in production and when their declared factory classes are unavailable. Local monorepo development continues to use factories when they are autoloadable.

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-product
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
