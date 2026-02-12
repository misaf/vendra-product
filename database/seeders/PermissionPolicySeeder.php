<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Misaf\VendraProduct\Enums\ProductCategoryPolicyEnum;
use Misaf\VendraProduct\Enums\ProductPolicyEnum;
use Misaf\VendraProduct\Enums\ProductPricePolicyEnum;
use Misaf\VendraTenant\Models\Tenant;
use Spatie\Permission\PermissionRegistrar;

final class PermissionPolicySeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->first();

        if ( ! $tenant) {
            $this->command?->error('Tenants not found. Please run TenantSeeder first.');

            return;
        }

        $tenant->makeCurrent();

        $this->seedPermissionPolicies($tenant);
    }

    private function seedPermissionPolicies(Tenant $tenant): void
    {
        $permissionModel = Config::string('permission.models.permission');
        $guardName = Config::string('auth.defaults.guard', 'web');
        $policies = array_values(array_unique([
            ...array_column(ProductPolicyEnum::cases(), 'value'),
            ...array_column(ProductCategoryPolicyEnum::cases(), 'value'),
            ...array_column(ProductPricePolicyEnum::cases(), 'value'),
        ]));

        $createdCount = 0;
        $existingCount = 0;

        foreach ($policies as $policy) {
            $permission = $permissionModel::query()->firstOrCreate([
                'name'       => $policy,
                'guard_name' => $guardName,
            ]);

            if ($permission->wasRecentlyCreated) {
                $createdCount++;
            } else {
                $existingCount++;
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info(sprintf('Successfully seeded %d product policy permissions for %s tenant. %d created, %d already existed.', count($policies), $tenant->slug, $createdCount, $existingCount));
    }
}
