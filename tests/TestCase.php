<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Tests;

use Illuminate\Support\Facades\Http;
use Misaf\VendraProduct\Providers\ProductServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Override;

abstract class TestCase extends OrchestraTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    protected function getPackageProviders($app): array
    {
        return [
            ProductServiceProvider::class,
        ];
    }
}
