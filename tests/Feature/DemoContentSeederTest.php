<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use Misaf\VendraProduct\Database\Seeders\DemoContentSeeder;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraProduct\Models\ProductPrice;

it('seeds its demo fixtures again without duplicating rows', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    makeCurrentTestTenant();

    Artisan::call('db:seed', ['--class' => DemoContentSeeder::class, '--force' => true]);

    $productCategories = ProductCategory::query()->count();
    $products = Product::query()->count();
    $productPrices = ProductPrice::query()->count();

    expect($productCategories)->toBeGreaterThan(0)
        ->and($products)->toBeGreaterThan(0)
        ->and($productPrices)->toBeGreaterThan(0);

    Artisan::call('db:seed', ['--class' => DemoContentSeeder::class, '--force' => true]);

    expect(ProductCategory::query()->count())->toBe($productCategories)
        ->and(Product::query()->count())->toBe($products)
        ->and(ProductPrice::query()->count())->toBe($productPrices);
});

it('stocks each product at the quantity its fixture declares', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    makeCurrentTestTenant();

    Artisan::call('db:seed', ['--class' => DemoContentSeeder::class, '--force' => true]);

    expect(Product::query()->where('slug->en', 'dell-xps-13')->sole()->quantity)->toBe(12)
        ->and(Product::query()->where('slug->en', 'apple-imac-24')->sole()->quantity)->toBe(0);
});

it('seeds bundled fixtures in a local application without package factory autoloading', function (): void {
    $result = Process::path(base_path())->env([
        'APP_ENV' => 'testing',
        'DB_CONNECTION' => 'sqlite',
        'DB_DATABASE' => ':memory:',
        'CACHE_STORE' => 'array',
        'QUEUE_CONNECTION' => 'sync',
        'SESSION_DRIVER' => 'array',
    ])->run([PHP_BINARY, '-r', <<<'PHP'
        $loader = require 'vendor/autoload.php';
        $app = require 'bootstrap/app.php';
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        makeCurrentTestTenant();
        $app->detectEnvironment(fn () => 'local');

        $productionLoader = new Composer\Autoload\ClassLoader;
        $factoryNamespace = 'Misaf\\VendraProduct\\Database\\Factories\\';
        foreach ($loader->getPrefixesPsr4() as $namespace => $paths) {
            if ($namespace !== $factoryNamespace) {
                $productionLoader->setPsr4($namespace, $paths);
            }
        }
        $productionLoader->addClassMap(array_filter(
            $loader->getClassMap(),
            fn ($class) => ! str_starts_with($class, $factoryNamespace),
            ARRAY_FILTER_USE_KEY,
        ));
        $loader->unregister();
        $productionLoader->register();

        if (class_exists(Misaf\VendraProduct\Database\Factories\ProductFactory::class)) {
            throw new RuntimeException('The consumer simulation still autoloads product factories.');
        }
        Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => Misaf\VendraProduct\Database\Seeders\DemoContentSeeder::class, '--force' => true], new Symfony\Component\Console\Output\NullOutput);
        echo Misaf\VendraProduct\Models\Product::query()->where('slug->en', 'dell-xps-13')->sole()->quantity;
        PHP]);

    expect($result->exitCode())->toBe(0, $result->errorOutput().$result->output())
        ->and(trim($result->output()))->toBe('12');
});
