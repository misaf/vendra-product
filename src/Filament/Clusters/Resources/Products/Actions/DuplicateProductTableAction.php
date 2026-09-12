<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions;

use Filament\Actions\ReplicateAction;
use InvalidArgumentException;
use Misaf\VendraProduct\Actions\BuildProductReplicaDataAction;
use Misaf\VendraProduct\Actions\DuplicateProductRelationsAction;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\ProductResource;
use Misaf\VendraProduct\Models\Product;

final class DuplicateProductTableAction extends ReplicateAction
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

        $this->mutateRecordDataUsing(function (array $data): array {
            $record = $this->getRecord();

            if (! $record instanceof Product) {
                throw new InvalidArgumentException('Duplicate action requires a product record.');
            }

            return resolve(BuildProductReplicaDataAction::class)->execute($record, $data);
        });

        $this->after(function (Product $record, Product $replica): void {
            resolve(DuplicateProductRelationsAction::class)->execute($record, $replica);
        });

        $this->successRedirectUrl(fn (Product $replica): string => ProductResource::getUrl('edit', ['record' => $replica]));
    }
}
