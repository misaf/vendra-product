<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions;

use Filament\Actions\ReplicateAction;
use Illuminate\Support\Str;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\ProductResource;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;
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
        $data['name'] = $this->duplicateTranslations($data['name'] ?? [], ' Copy');
        $data['slug'] = $this->duplicateTranslations($data['slug'] ?? [], '-copy', slug: true);

        return $data;
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
            ->where('collection_name', 'products')
            ->get()
            ->each(fn(Media $media): Media => $media->copy($replica, $media->collection_name, $media->disk));
    }
}
