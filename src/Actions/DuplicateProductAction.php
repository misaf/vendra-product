<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraProduct\Models\Product;

final readonly class DuplicateProductAction
{
    public function __construct(
        private BuildProductReplicaDataAction $replicaData,
        private DuplicateProductRelationsAction $relations,
    ) {}

    public function execute(Product $product): Product
    {
        return DB::transaction(function () use ($product): Product {
            /** @var Product $replica */
            $replica = $product->replicate(['position', 'token']);

            $replica->forceFill($this->replicaData->execute($product, $product->toArray()));
            $replica->save();

            $this->relations->execute($product, $replica);

            return $replica;
        });
    }
}
