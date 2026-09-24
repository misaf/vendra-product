<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Actions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;
use Misaf\VendraSupport\Capabilities\AttributeIntegration;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Tenancy\Scopes\TenantScope;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use Misaf\VendraSupport\Tenancy\TenantSchema;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class DuplicateProductAction
{
    public function execute(Product $product): Product
    {
        return DB::transaction(function () use ($product): Product {
            /** @var Product $replica */
            $replica = $product->replicate(['position', 'token']);
            $replica->forceFill($this->copyNames($product));
            $replica->save();

            $this->duplicatePrices($product, $replica);
            $this->duplicateMedia($product, $replica);
            $this->duplicateAttributeValueSelections($product, $replica);
            $this->duplicateTags($product, $replica);

            return $replica;
        });
    }

    /**
     * Give the copy a name and slug no other product in its tenant uses.
     *
     * @return array<string, array<string, string>>
     */
    private function copyNames(Product $product): array
    {
        $transformed = [];

        $name = $this->ensureUniqueTranslatedValue($product, 'name', $this->duplicateTranslations($product->getTranslations('name'), ' Copy'));
        if ($name !== []) {
            $transformed['name'] = $name;
        }

        $slug = $this->ensureUniqueTranslatedValue($product, 'slug', $this->duplicateTranslations($product->getTranslations('slug'), '-copy', slug: true), slug: true);
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
     * @param  array<string, string>  $translations
     * @return array<string, array<string, true>>
     */
    private function existingTranslatedValues(Product $product, string $column, array $translations): array
    {
        $query = Product::withTrashed()->whereKeyNot($product->getKey());

        // Scope to the source record's tenant, which may differ from the current one.
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
                if (is_string($locale) && is_string($value)) {
                    $existing[$locale][$value] = true;
                }
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

    private function duplicatePrices(Product $source, Product $replica): void
    {
        $source->productPrices()
            ->get()
            ->each(fn (ProductPrice $productPrice): ProductPrice => $replica->productPrices()->create([
                'currency_code' => $productPrice->currency_code,
                'price' => (int) $productPrice->price->getAmount(),
            ]));
    }

    private function duplicateMedia(Product $source, Product $replica): void
    {
        $source->media()
            ->where('collection_name', Product::MEDIA_COLLECTION)
            ->get()
            ->each(fn (Media $media): Media => $media->copy($replica, $media->collection_name, $media->disk));
    }

    private function duplicateAttributeValueSelections(Product $source, Product $replica): void
    {
        if (AttributeIntegration::valueModel() === null) {
            return;
        }

        $selectedAttributeValueIds = $source->selectedAttributeValues()->allRelatedIds();

        if ($selectedAttributeValueIds->isNotEmpty()) {
            $replica->selectedAttributeValues()->attach($selectedAttributeValueIds->all());
        }
    }

    private function duplicateTags(Product $source, Product $replica): void
    {
        if (! TagIntegration::isAvailable()) {
            return;
        }

        $tagIds = $source->tags()->pluck($source->tags()->getRelated()->getQualifiedKeyName());

        if ($tagIds->isNotEmpty()) {
            $replica->tags()->attach($tagIds->all());
        }
    }
}
