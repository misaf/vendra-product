<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->laravel();

arch('the product module derives tenancy from the support layer, never a concrete tenant provider')
    ->expect('Misaf\VendraProduct')
    ->not->toUse('Misaf\VendraTenant');

arch('the product module derives currency support from the support layer, never the currency module')
    ->expect('Misaf\VendraProduct')
    ->not->toUse('Misaf\VendraCurrency');

arch('the product module integrates attributes through support, never the attribute module')
    ->expect('Misaf\VendraProduct')
    ->not->toUse('Misaf\VendraAttribute');

arch('the product module integrates tags through support, never the tagger or Spatie tags modules')
    ->expect('Misaf\VendraProduct')
    ->not->toUse([
        'Misaf\VendraTagger',
        'Spatie\Tags',
    ]);
