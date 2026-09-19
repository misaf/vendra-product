<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions;

use Filament\Actions\ReplicateAction;
use Misaf\VendraProduct\Actions\DuplicateProductAction;
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

        $this->action(function (Product $record): void {
            $this->replica = resolve(DuplicateProductAction::class)->execute($record);

            $this->success();
        });

        $this->successRedirectUrl(fn (Product $replica): string => ProductResource::getUrl('edit', ['record' => $replica]));
    }
}
