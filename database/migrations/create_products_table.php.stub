<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraSupport\Tenancy\TenantSchema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            $this->createProductCategoriesTable();
            $this->createProductsTable();
            $this->createProductPricesTable();
        });
    }

    private function createProductCategoriesTable(): void
    {
        Schema::create('product_categories', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->json('name');
            $table->json('description')
                ->nullable();
            $table->json('slug');
            $table->unsignedBigInteger('position');
            $table->boolean('active')
                ->default(false);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(TenantSchema::tenantIndex(['position']));
            $table->index(TenantSchema::tenantIndex(['active']));
        });
    }

    private function createProductsTable(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->foreignId('product_category_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->json('name');
            $table->json('description')
                ->nullable();
            $table->json('slug');
            $table->string('token');
            $table->integer('quantity');
            $table->integer('stock_threshold')
                ->nullable();
            $table->boolean('in_stock')
                ->default(false);
            $table->unsignedBigInteger('position');
            $table->boolean('available_soon')
                ->default(false);
            $table->timestampTz('availability_date')
                ->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(TenantSchema::tenantIndex(['product_category_id']));
            $table->unique(TenantSchema::tenantIndex(['token']));
            $table->index(TenantSchema::tenantIndex(['quantity']));
            $table->index(TenantSchema::tenantIndex(['stock_threshold']));
            $table->index(TenantSchema::tenantIndex(['in_stock']));
            $table->index(TenantSchema::tenantIndex(['position']));
            $table->index(TenantSchema::tenantIndex(['available_soon']));
        });
    }

    private function createProductPricesTable(): void
    {
        Schema::create('product_prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->char('currency_code', 3)
                ->default(Config::string('app.currency'));
            $table->unsignedBigInteger('price')
                ->default(0);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('product_id');
            $table->index('currency_code');
            $table->index('price');
        });
    }

    public function down(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::dropIfExists('product_prices');
            Schema::dropIfExists('products');
            Schema::dropIfExists('product_categories');
        });
    }
};
