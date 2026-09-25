<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions;

use Filament\Actions\CreateAction;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\Concerns\DisablesAtProductLimit;

final class CreateProductPageAction extends CreateAction
{
    use DisablesAtProductLimit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->disableAtProductLimit();
    }
}
