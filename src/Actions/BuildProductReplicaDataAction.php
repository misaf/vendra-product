<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Actions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraSupport\Tenancy\Scopes\TenantScope;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use Misaf\VendraSupport\Tenancy\TenantSchema;

final class BuildProductReplicaDataAction
{
    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    public function execute(Product $product, array $data): array
    {
        $transformed = [];

        $name = $this->ensureUniqueTranslatedValue($product, 'name', $this->duplicateTranslations(Arr::get($data, 'name', null), ' Copy'));
        if ($name !== []) {
            $transformed['name'] = $name;
        }

        $slug = $this->ensureUniqueTranslatedValue($product, 'slug', $this->duplicateTranslations(Arr::get($data, 'slug', null), '-copy', slug: true), slug: true);
        if ($slug !== []) {
            $transformed['slug'] = $slug;
        }

        return $transformed;
    }

    /**
     * @param  array<string, string>  $translations
     * @return array<string, string>
     */
    private function ensureUniqueTranslatedValue(Product $product, string $column, array $translations, bool $slug = false): array
    {
        if ($translations === []) {
            return $translations;
        }

        $existing = $this->existingTranslatedValues($product, $column, $translations);

        $candidate = $translations;
        $counter = 1;

        while ($this->translatedValueTaken($candidate, $existing)) {
            $counter++;
            $candidate = [];

            foreach ($translations as $locale => $value) {
                $candidate[$locale] = $slug
                    ? Str::slug("{$value}-{$counter}")
                    : "{$value} {$counter}";
            }
        }

        return $candidate;
    }

    /**
     * Fetch every existing translation of the column that could collide with the
     * base translations or any counter-suffixed variant of them, in one query.
     *
     * @param  array<string, string>  $translations
     * @return array<string, array<string, true>>
     */
    private function existingTranslatedValues(Product $product, string $column, array $translations): array
    {
        $query = Product::withTrashed()->whereKeyNot($product->getKey());

        // Anchor collision detection to the source record's tenant explicitly
        // rather than relying on the ambient tenant global scope, which may
        // point at a different tenant while the action runs. The replica always
        // belongs to the source record's tenant, so scope the lookup to that
        // same tenant.
        if (TenantAwareness::enabled()) {
            $tenantColumn = TenantSchema::column();
            $tenantId = $product->getAttribute($tenantColumn) ?? TenantAwareness::currentId();

            $query
                ->withoutGlobalScope(TenantScope::class)
                ->where((new Product)->qualifyColumn($tenantColumn), $tenantId);
        }

        $query->where(function (Builder $query) use ($column, $translations): void {
            foreach ($translations as $locale => $value) {
                $query->orWhere("{$column}->{$locale}", 'like', addcslashes($value, '\\%_').'%');
            }
        });

        $existing = [];

        foreach ($query->get([$column]) as $existingProduct) {
            foreach ($existingProduct->getTranslations($column) as $locale => $value) {
                $existing[$locale][$value] = true;
            }
        }

        return $existing;
    }

    /**
     * @param  array<string, string>  $candidate
     * @param  array<string, array<string, true>>  $existing
     */
    private function translatedValueTaken(array $candidate, array $existing): bool
    {
        return array_any($candidate, fn ($value, $locale) => isset($existing[$locale][$value]));
    }

    /**
     * @return array<string, string>
     */
    private function duplicateTranslations(mixed $translations, string $suffix, bool $slug = false): array
    {
        if (! is_array($translations)) {
            return [];
        }

        $duplicatedTranslations = [];

        foreach ($translations as $locale => $translation) {
            if (! is_string($locale) || ! is_string($translation) || $translation === '') {
                continue;
            }

            $duplicatedTranslations[$locale] = $slug
                ? Str::slug($translation.$suffix)
                : $translation.$suffix;
        }

        return $duplicatedTranslations;
    }
}
