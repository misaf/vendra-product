<?php

declare(strict_types=1);
use Misaf\VendraProductApi\JsonApi\V1\Server;

return [
    'namespace' => 'JsonApi',

    'servers' => [
        'vendra-product' => Server::class,
    ],
];
