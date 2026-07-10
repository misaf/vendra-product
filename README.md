# Vendra Product

Tenant-aware product management for Vendra applications.

## Features

- Product categories
- Products
- Product prices per currency
- Filament resources on the `admin` panel

## Requirements

- PHP 8.3+
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

## Installation

```bash
composer require misaf/vendra-product
php artisan vendor:publish --tag=vendra-product-migrations
php artisan migrate
```

Optional translations publish:

```bash
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
    'status' => true,
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

## Filament

Resources are available in the `Products` cluster on the `admin` panel:

- Product Categories
- Products
- Product Prices

## Testing

```bash
composer test
```

## License

MIT. See [LICENSE](LICENSE).
