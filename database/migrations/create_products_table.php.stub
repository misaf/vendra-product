<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        $this->createProductCategoriesTable();
        $this->createProductsTable();
        $this->createProductPricesTable();
        Schema::enableForeignKeyConstraints();
    }
    
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
        Schema::enableForeignKeyConstraints();
    }

    private function createProductCategoriesTable(): void
    {
        Schema::create('product_categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->json('name');
            $table->json('description')
                ->nullable();
            $table->json('slug');
            $table->unsignedBigInteger('position');
            $table->boolean('status')
                ->default(false);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['tenant_id', 'position']);
            $table->index(['tenant_id', 'status']);
        });
    }

    private function createProductsTable(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('product_category_id');
            $table->json('name');
            $table->json('description')
                ->nullable();
            $table->json('slug');
            $table->string('token');
            $table->integer('quantity')
                ->nullable();
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

            $table->index(['tenant_id', 'product_category_id']);
            $table->index(['tenant_id', 'token']);
            $table->index(['tenant_id', 'quantity']);
            $table->index(['tenant_id', 'stock_threshold']);
            $table->index(['tenant_id', 'in_stock']);
            $table->index(['tenant_id', 'position']);
            $table->index(['tenant_id', 'available_soon']);
        });
    }

    private function createProductPricesTable(): void
    {
        Schema::create('product_prices', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id');
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
};
