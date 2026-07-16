<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions;

use Filament\Actions\BulkAction;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;

final class SetPriceAction extends BulkAction
{
    use CanCustomizeProcess;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('vendra-product::actions.set_price'));

        $this->successNotificationTitle(__('filament-actions::edit.single.notifications.saved.title'));

        $this->color('primary');

        $this->icon('heroicon-o-archive-box-arrow-down');

        $this->requiresConfirmation();

        $this->modalIcon('heroicon-o-archive-box-arrow-down');

        $this->schema([
            Select::make('currency_code')
                ->columnSpanFull()
                ->default(fn(): string => ProductPrice::defaultCurrencyCode())
                ->label(__('vendra-product::attributes.currency'))
                ->native(false)
                ->options(fn(): array => ProductPrice::currencyOptions())
                ->preload()
                ->required()
                ->searchable(),

            TextInput::make('price')
                ->autofocus()
                ->columnSpanFull()
                ->label(__('vendra-product::attributes.price'))
                ->live(onBlur: true)
                ->mask(RawJs::make('$money($input)'))
                ->numeric()
                ->required()
                ->stripCharacters(','),
        ]);

        $this->action(function (array $data): void {
            $currencyCode = $data['currency_code'] ?? null;
            $price = $data['price'] ?? null;

            if ( ! is_string($currencyCode) || '' === $currencyCode) {
                throw new InvalidArgumentException('Invalid currency code provided.');
            }

            if ( ! is_numeric($price)) {
                throw new InvalidArgumentException('Invalid price provided.');
            }

            $this->process(static function (Collection $records) use ($currencyCode, $price): void {
                foreach ($records as $record) {
                    if ( ! $record instanceof Product) {
                        continue;
                    }

                    $record->productPrices()->create([
                        'currency_code' => $currencyCode,
                        'price'         => $price,
                    ]);
                }
            });

            $this->success();
        });

        $this->deselectRecordsAfterCompletion();
    }

    public static function getDefaultName(): string
    {
        return 'setPrice';
    }
}
