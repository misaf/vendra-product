<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;
use Misaf\VendraSupport\Capabilities\AttributeIntegration;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class DuplicateProductRelationsAction
{
    public function execute(Product $source, Product $replica): void
    {
        DB::transaction(function () use ($source, $replica): void {
            $this->duplicatePrices($source, $replica);
            $this->duplicateMedia($source, $replica);
            $this->duplicateAttributeValueSelections($source, $replica);
            $this->duplicateTags($source, $replica);
        });
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
