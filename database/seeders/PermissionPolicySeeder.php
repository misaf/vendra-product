<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Database\Seeders;

use Misaf\VendraProduct\Enums\ProductCategoryPolicyEnum;
use Misaf\VendraProduct\Enums\ProductPolicyEnum;
use Misaf\VendraProduct\Enums\ProductPricePolicyEnum;
use Misaf\VendraProduct\ProductPlugin;
use Misaf\VendraSupport\Database\Seeders\PermissionPolicySeeder as BasePermissionPolicySeeder;
use Misaf\VendraTenant\Concerns\RequiresCurrentTenant;

final class PermissionPolicySeeder extends BasePermissionPolicySeeder
{
    use RequiresCurrentTenant;

    protected const string MODULE_NAME = ProductPlugin::ID;

    public function run(): void
    {
        $tenant = $this->currentTenant();

        $this->seedPermissionPolicies($tenant->getKey());
    }

    /**
     * @return list<string>
     */
    protected function policies(): array
    {
        return [
            ...array_column(ProductCategoryPolicyEnum::cases(), 'value'),
            ...array_column(ProductPolicyEnum::cases(), 'value'),
            ...array_column(ProductPricePolicyEnum::cases(), 'value'),
        ];
    }
}
