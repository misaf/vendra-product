## Vendra Product

The `misaf/vendra-product` package owns products, product categories, and pricing and the Filament admin UI for products, categories, and prices.

### Standards

- Keep product domain code inside `packages/vendra-product` using the `Misaf\VendraProduct` namespace.
- Use this package for models, migrations, factories, seeders, policies, permission enums, observers, Filament resources, translations, config, and package bootstrapping.
- Keep JSON:API serialization and API routes in `misaf/vendra-product-api`.
- Follow existing model conventions where they apply: tenant ownership, translated `name` / `description` / `slug`, soft deletes, sortable `position`, media collections, factories, and typed relationships.
- Tenant awareness is owned by `misaf/vendra-support` via `Misaf\VendraSupport\Support\TenantAwareness`, which derives purely from the bound `TenantResolver`. Installing a tenant provider (e.g. `misaf/vendra-tenant`) makes the app tenant-aware; without one the default null resolver keeps it disabled. The module defines no `tenant_aware` config.
- Keep the module tenant-agnostic: it must build and run with or without a tenant provider. Never reference a concrete provider such as `Misaf\VendraTenant` anywhere — models, migrations, factories, seeders, or fixtures. Let `BelongsToTenant` assign `tenant_id`; do not set it manually.
- Keep Filament resources thin by delegating forms to `Schemas/*Form.php` and tables to `Tables/*Table.php`.
- Follow Laravel comment style: document with PHPDoc (array shapes, generics, `@see`) and reserve inline comments for genuinely complex logic. Match the surrounding file and do not add comments that restate the code.
- Add or update Pest tests for policy coverage, config/navigation behavior, translation parity, model contracts, and user-visible Filament behavior.
- Keep tests purposeful and prevent unnecessary ones: cover behavior, contracts, and edge cases — not framework internals or trivially typed code. Do not duplicate coverage a focused test already proves, and do not add throwaway verification scripts when a test fits.
- Keep Pest architecture tests in `tests/ArchTest.php`: the `php`, `security`, and `laravel` presets plus a tenant-agnostic expectation, e.g. `arch()->expect('Misaf\VendraProduct')->not->toUse('Misaf\VendraTenant')`.
