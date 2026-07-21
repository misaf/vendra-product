<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions;

use Filament\Actions\ReplicateAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\ProductResource;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;
use Misaf\VendraSupport\Support\AttributeIntegration;
use Misaf\VendraSupport\Support\TagIntegration;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class DuplicateProductAction extends ReplicateAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('vendra-product::actions.duplicate'));

        $this->modalHeading(__('vendra-product::actions.duplicate_product'));

        $this->modalSubmitActionLabel(__('vendra-product::actions.duplicate'));

        $this->successNotificationTitle(__('vendra-product::messages.product_duplicated'));

        $this->authorize('replicate');

        $this->requiresConfirmation();

        $this->excludeAttributes(['position', 'token']);

        $this->mutateRecordDataUsing(fn(array $data): array => $this->mutateReplicaData($data));

        $this->after(function (Product $record, Product $replica): void {
            $this->duplicateRelations($record, $replica);
        });

        $this->successRedirectUrl(fn(Product $replica): string => ProductResource::getUrl('edit', ['record' => $replica]));
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    private function mutateReplicaData(array $data): array
    {
        $transformed = [];

        $name = $this->ensureUniqueTranslatedValue('name', $this->duplicateTranslations($data['name'] ?? null, ' Copy'));
        if ([] !== $name) {
            $transformed['name'] = $name;
        }

        $slug = $this->ensureUniqueTranslatedValue('slug', $this->duplicateTranslations($data['slug'] ?? null, '-copy', slug: true), slug: true);
        if ([] !== $slug) {
            $transformed['slug'] = $slug;
        }

        return $transformed;
    }

    /**
     * @param  array<string, string>  $translations
     * @return array<string, string>
     */
    private function ensureUniqueTranslatedValue(string $column, array $translations, bool $slug = false): array
    {
        if ([] === $translations) {
            return $translations;
        }

        $existing = $this->existingTranslatedValues($column, $translations);

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
    private function existingTranslatedValues(string $column, array $translations): array
    {
        $query = Product::withTrashed();

        $originalRecord = $this->getRecord();
        if (null !== $originalRecord && $originalRecord->exists) {
            $query->whereKeyNot($originalRecord->getKey());
        }

        $query->where(function (Builder $query) use ($column, $translations): void {
            foreach ($translations as $locale => $value) {
                $query->orWhere("{$column}->{$locale}", 'like', addcslashes($value, '\\%_') . '%');
            }
        });

        $existing = [];

        foreach ($query->get([$column]) as $product) {
            foreach ($product->getTranslations($column) as $locale => $value) {
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
        foreach ($candidate as $locale => $value) {
            if (isset($existing[$locale][$value])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    private function duplicateTranslations(mixed $translations, string $suffix, bool $slug = false): array
    {
        if ( ! is_array($translations)) {
            return [];
        }

        $duplicatedTranslations = [];

        foreach ($translations as $locale => $translation) {
            if ( ! is_string($locale) || ! is_string($translation) || '' === $translation) {
                continue;
            }

            $duplicatedTranslations[$locale] = $slug
                ? Str::slug($translation . $suffix)
                : $translation . $suffix;
        }

        return $duplicatedTranslations;
    }

    private function duplicateRelations(Product $record, Product $replica): void
    {
        $this->duplicatePrices($record, $replica);
        $this->duplicateMedia($record, $replica);
        $this->duplicateAttributeValueSelections($record, $replica);
        $this->duplicateTags($record, $replica);
    }

    private function duplicatePrices(Product $record, Product $replica): void
    {
        $record->productPrices()
            ->get()
            ->each(fn(ProductPrice $productPrice): ProductPrice => $replica->productPrices()->create([
                'currency_code' => $productPrice->currency_code,
                'price'         => (int) $productPrice->price->getAmount(),
            ]));
    }

    private function duplicateMedia(Product $record, Product $replica): void
    {
        $record->media()
            ->where('collection_name', Product::MEDIA_COLLECTION)
            ->get()
            ->each(fn(Media $media): Media => $media->copy($replica, $media->collection_name, $media->disk));
    }

    private function duplicateAttributeValueSelections(Product $record, Product $replica): void
    {
        if (null === AttributeIntegration::valueModel()) {
            return;
        }

        $selectedAttributeValueIds = $record->selectedAttributeValues()->allRelatedIds();

        if ($selectedAttributeValueIds->isNotEmpty()) {
            $replica->selectedAttributeValues()->attach($selectedAttributeValueIds->all());
        }
    }

    private function duplicateTags(Product $record, Product $replica): void
    {
        if ( ! TagIntegration::isAvailable()) {
            return;
        }

        $tagIds = $record->tags()->pluck($record->tags()->getRelated()->getQualifiedKeyName());

        if ($tagIds->isNotEmpty()) {
            $replica->tags()->attach($tagIds->all());
        }
    }
}
