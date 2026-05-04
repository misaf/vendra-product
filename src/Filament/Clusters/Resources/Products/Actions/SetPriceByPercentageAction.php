<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions;

use Filament\Actions\BulkAction;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use Misaf\VendraProduct\Models\Product;

final class SetPriceByPercentageAction extends BulkAction
{
    use CanCustomizeProcess;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('vendra-product::actions.set_price_by_percentage'));

        $this->successNotificationTitle(__('filament-actions::edit.single.notifications.saved.title'));

        $this->color('primary');

        $this->icon('heroicon-o-archive-box-arrow-down');

        $this->requiresConfirmation();

        $this->modalIcon('heroicon-o-archive-box-arrow-down');

        $this->schema([
            TextInput::make('percent')
                ->autofocus()
                ->columnSpanFull()
                ->label(__('vendra-product::actions.percentage'))
                ->numeric()
                ->required()
                ->stripCharacters(','),
        ]);

        $this->action(function (array $data): void {
            $percent = $data['percent'] ?? null;

            if ( ! is_numeric($percent)) {
                throw new InvalidArgumentException('Invalid percent provided.');
            }

            $this->process(static function (Collection $records) use ($percent): void {
                foreach ($records as $record) {
                    if ( ! $record instanceof Product) {
                        continue;
                    }

                    $latestProductPrice = $record->latestProductPrice->price;

                    if ($percent < 0) {
                        $newPrice = $latestProductPrice->getAmount()->__toString() * (1 - abs((int) $percent) / 100);
                    } else {
                        $newPrice = $latestProductPrice->getAmount()->__toString() * (1 + $percent / 100);
                    }

                    $record->productPrices()->create([
                        'price' => $newPrice,
                    ]);
                }
            });

            $this->success();
        });

        $this->deselectRecordsAfterCompletion();
    }

    public static function getDefaultName(): string
    {
        return 'setPriceByPercentage';
    }
}
