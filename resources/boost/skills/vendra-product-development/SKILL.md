---
name: vendra-product-development
description: "Create, modify, review, or test the Vendra Product package in packages/vendra-product. Use for Product, ProductCategory, ProductPrice, translated catalog data, media and optional product tags, observers, migrations, factories, seeders, policies, Filament resources, configuration, translations, package wiring, and the vendra-product-api boundary."
---

# Vendra Product

## Workflow

- Inspect `composer.json`, sibling files, and existing tests before changing the package.
- Use Laravel Boost `application-info` and `search-docs` before code changes.
- Apply `laravel-best-practices` to Laravel PHP and `pest-testing` whenever tests change.
- Apply `tailwindcss-development` only when changing Blade markup or Tailwind classes.
- Keep changes inside this package's boundary and preserve its public contracts.
- Add or update focused Pest coverage, then run `composer --working-dir=packages/vendra-product test` and `composer --working-dir=packages/vendra-product analyse`.

## Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

## Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.

## Module Boundary

Treat `packages/vendra-product` as the source of product domain behavior and Filament admin UI.

- Use namespace `Misaf\VendraProduct`.
- Keep domain models, factories, seeders, policies, observers, console commands, Filament classes, config, migrations, translations, and tests inside this module.
- Do not place product domain code in the host app unless the host app is only integrating the module.
- Keep API serialization and JSON:API route behavior out of this module; use `vendra-product-api` for that.
- Keep cross-module dependencies explicit in `composer.json`; do not introduce a dependency without approval.
- Tag-consuming models must use `Misaf\VendraSupport\Capabilities\HasOptionalTags` as the single source of their `tags()` relationship and pivot metadata. Keep the package tag-agnostic: define a stable package-owned tag type, use `TagIntegration` for availability and UI integration, never import the concrete Vendra Tagger model/provider or define the relationship through Spatie `HasTags`, and list Tagger only under Composer `suggest`.

## Domain Model Standards

Follow the existing `Product` and `ProductCategory` patterns for new product entities.

- Use `declare(strict_types=1)`, final classes, typed method signatures, and PHPDoc generics for relationships.
- Follow Laravel comment style: document with PHPDoc (array shapes, generics, `@see`) and reserve inline comments for genuinely complex logic. Match the surrounding file's density and do not add comments that restate the code.
- Prefer the Laravel attributes already used here, such as `#[Fillable]`, `#[Hidden]`, `#[UseFactory]`, and `#[ObservedBy]`.
- Keep the module tenant-agnostic: derive tenant awareness purely from the bound `TenantResolver` in `misaf/vendra-support` (`TenantAwareness`, `BelongsToTenant`, `TenantSchema`, `RequiresCurrentTenant`). The module must build and run whether or not a tenant provider is installed, so never reference a concrete provider such as `Misaf\VendraTenant` anywhere — models, migrations, factories, seeders, or fixtures. There is no `tenant_aware` config toggle.
- Hide `tenant_id` and keep tenant behavior centralized in the support layer; do not duplicate tenant scoping or `tenant_id` assignment in models, Filament resources, factories, or seeders. `BelongsToTenant` assigns `tenant_id` on `creating` from the current tenant.
- Reuse only the traits and conventions present on the affected sibling model; do not infer translations, media, slugs, sorting, or soft deletes from another package.

## Filament Standards

Keep every resource that declares a `$cluster`, including its complete supporting tree, under `src/Filament/Clusters/Resources/` with the matching `Misaf\VendraProduct\Filament\Clusters\Resources` namespace and plugin discovery path. Resources without a cluster belong under `src/Filament/Resources/`.

- Register module UI through the module `Plugin` and `ServiceProvider`; do not manually wire resources in unrelated panel providers.
- Keep resource classes thin. Delegate form schemas to `Schemas/*Form.php` and table configuration to `Tables/*Table.php`.
- Use Filament v5 namespaces: form fields from `Filament\Forms\Components`, layout from `Filament\Schemas\Components`, table columns from `Filament\Tables\Columns`, filters from `Filament\Tables\Filters`, actions from `Filament\Actions`, and icons from `Filament\Support\Icons\Heroicon`.
- Use this module's translation keys (`vendra-product::attributes`, `vendra-product::navigation`) for labels, breadcrumbs, and navigation.
- Keep cluster resources ungrouped and assign `$navigationSort` from their package-specific `NavigationPriority` cases; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Prevent N+1 issues in tables and relation managers with eager loading, `withCount`, or computed state based on loaded relationships.
- Use public media visibility only when public access is actually required.

## Permissions And Navigation

Use policy enums and policies as the permission source.

- Add enum cases for every resource action the panel exposes.
- Keep policy method names aligned with Filament actions: `viewAny`, `view`, `create`, `update`, `delete`, `deleteAny`, `restore`, `restoreAny`, `forceDelete`, `forceDeleteAny`, `replicate`, and `reorder` as applicable.
- Update `PermissionPolicySeeder` when new permissions are introduced.
- Keep navigation labels and groups configurable through the module `Plugin` and `config/vendra-product.php`. Do not add a `tenant_aware` config value; tenant awareness derives from the bound `TenantResolver`.

## Data And Localization

Migrations, factories, seeders, and translation files are part of the contract.

- Use package migrations in `database/migrations`, with stubs only when the install flow expects publishing.
- Use factories under `database/factories` and seeders under `database/seeders`. Keep them tenant-safe: import no concrete tenant provider and set no `tenant_id` directly; let `BelongsToTenant` assign it from the current tenant so they work with tenancy on or off.
- Keep demo fixtures deterministic and tenant-safe.
- Update all supported locales together and keep translation keys sorted.
- Preserve translation key parity tests when adding labels or attributes.

## Testing And Verification

Prefer focused Pest tests in the module.

- Keep tests purposeful and prevent unnecessary ones: cover behavior, contracts, and edge cases — not framework internals or trivially typed code. Do not duplicate coverage a focused test already proves, and do not add throwaway verification scripts (or `tinker`) when a test fits.
- Keep an architecture expectation proving Product does not use Vendra Tagger or Spatie Tags directly. Test both the unavailable fallback and the support-resolved polymorphic relation.
- Add or update unit tests for model contracts, policy permission coverage, resolver-derived tenant awareness, navigation/config behavior, and translation parity.
- Keep Pest architecture tests in `tests/ArchTest.php`: the `php`, `security`, and `laravel` presets, plus an expectation that the module stays tenant-agnostic, e.g. `arch()->expect('Misaf\VendraProduct')->not->toUse('Misaf\VendraTenant')`.
- Add feature or Livewire tests when changing Filament behavior with meaningful user-visible effects.
- Run module checks from the package when possible: `composer --working-dir=packages/vendra-product test` and `composer --working-dir=packages/vendra-product analyse`.
- If PHP files changed, run Pint for the touched code: `vendor/bin/pint --dirty --format agent` from the host app, or the module formatter if working only inside the package.
