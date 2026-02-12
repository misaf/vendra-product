<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions;

use Filament\Actions\BulkAction;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Illuminate\Database\Eloquent\Collection;
use Misaf\VendraProduct\Models\Product;

final class OutOfStockAction extends BulkAction
{
    use CanCustomizeProcess;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('vendra-product::actions.out_of_stock'));

        $this->successNotificationTitle(__('filament-actions::edit.single.notifications.saved.title'));

        $this->color('gray');

        $this->icon('heroicon-o-archive-box-x-mark');

        $this->requiresConfirmation();

        $this->modalIcon('heroicon-o-archive-box-x-mark');

        $this->action(function (): void {
            $this->process(static function (Collection $records): void {
                foreach ($records as $record) {
                    if ( ! $record instanceof Product) {
                        continue;
                    }

                    $record->update(['in_stock' => false]);
                }
            });

            $this->success();
        });

        $this->deselectRecordsAfterCompletion();
    }

    public static function getDefaultName(): string
    {
        return 'outOfStock';
    }
}
