<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions;

use Filament\Actions\BulkAction;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Illuminate\Database\Eloquent\Collection;
use Misaf\VendraProduct\Models\Product;

final class InStockAction extends BulkAction
{
    use CanCustomizeProcess;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('vendra-product::actions.in_stock'));

        $this->successNotificationTitle(__('filament-actions::edit.single.notifications.saved.title'));

        $this->color('primary');

        $this->icon('heroicon-o-archive-box-arrow-down');

        $this->requiresConfirmation();

        $this->modalIcon('heroicon-o-archive-box-arrow-down');

        $this->action(function (): void {
            $this->process(static function (Collection $records): void {
                foreach ($records as $record) {
                    if ( ! $record instanceof Product) {
                        continue;
                    }

                    $record->update([
                        'in_stock'          => true,
                        'available_soon'    => false,
                        'availability_date' => null,
                    ]);
                }
            });

            $this->success();
        });

        $this->deselectRecordsAfterCompletion();
    }

    public static function getDefaultName(): string
    {
        return 'inStock';
    }
}
