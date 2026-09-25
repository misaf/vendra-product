<?php

declare(strict_types=1);

use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use Misaf\VendraProduct\Database\Factories\ProductCategoryFactory;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\CreateProduct;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\ListProducts;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;
use Misaf\VendraSupport\Contracts\TenantEntitlements;
use Misaf\VendraSupport\Enums\PlanFeature;
use Misaf\VendraSupport\Enums\PlanLimit;
use Misaf\VendraSupport\Exceptions\EntitlementExceededException;
use Misaf\VendraSupport\Tenancy\TenantUsageRegistry;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentAdminTestContext();

    Filament::getPanel('admin')->plugin(
        SpatieTranslatablePlugin::make()->defaultLocales(['en', 'de']),
    );
});

function capProductsAtCurrentUsage(): void
{
    app()->instance(TenantEntitlements::class, new class implements TenantEntitlements
    {
        public function allows(PlanFeature $feature, ?Model $tenant = null): bool
        {
            return true;
        }

        public function limit(PlanLimit $limit, ?Model $tenant = null): ?int
        {
            return 0;
        }

        public function assertAllows(PlanFeature $feature, ?Model $tenant = null): void {}

        public function canAdd(PlanLimit $limit, int $amount = 1, ?Model $tenant = null): bool
        {
            return false;
        }

        public function assertCanAdd(PlanLimit $limit, int $amount = 1, ?Model $tenant = null): void
        {
            throw EntitlementExceededException::limitReached($limit, 0);
        }

        public function recordAdded(PlanLimit $limit, int $amount = 1, ?Model $tenant = null): void {}
    });
}

it('counts only the given store products toward the plan limit', function (): void {
    $store = currentTestTenant();
    ProductFactory::new()->count(2)->create();

    $otherStore = createTestTenant();
    switchToTestTenant($otherStore);
    Filament::setTenant($otherStore);
    ProductFactory::new()->create();

    expect(resolve(TenantUsageRegistry::class)->usage(PlanLimit::ProductsPerStore, $store))->toBe(2)
        ->and(resolve(TenantUsageRegistry::class)->usage(PlanLimit::ProductsPerStore, $otherStore))->toBe(1);
});

it('refuses a product past the plan limit on every create path', function (): void {
    capProductsAtCurrentUsage();

    ProductFactory::new()->create();
})->throws(EntitlementExceededException::class);

it('tells the user when the create page is past the plan limit', function (): void {
    $productCategory = ProductCategoryFactory::new()->create();
    capProductsAtCurrentUsage();

    livewire(CreateProduct::class)
        ->fillForm([
            'product_category_id' => $productCategory->getKey(),
            'name' => 'One product too many',
            'slug' => 'one-product-too-many',
            'description' => 'A product the plan does not allow.',
            'currency_code' => ProductPrice::defaultCurrencyCode(),
            'price' => 100,
            'quantity' => 1,
        ])
        ->call('create')
        ->assertNotified(__('vendra-support::entitlements.limit_reached', ['limit' => PlanLimit::ProductsPerStore->getLabel(), 'allowed' => 0]));

    expect(Product::query()->count())->toBe(0);
});

it('disables creating and duplicating products at the plan limit', function (): void {
    $product = ProductFactory::new()->create();

    livewire(ListProducts::class)
        ->assertActionEnabled('create')
        ->assertActionEnabled(TestAction::make('replicate')->table($product));

    capProductsAtCurrentUsage();

    livewire(ListProducts::class)
        ->assertActionDisabled('create')
        ->assertActionDisabled(TestAction::make('replicate')->table($product));
});
