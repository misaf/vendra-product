<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Filament Panels
    |--------------------------------------------------------------------------
    |
    | Here you may specify the Filament panels that the product administration UI
    | is registered on. The product navigation only appears within the panels
    | listed here. You may provide a single panel ID or an array of IDs to
    | mount the module across multiple panels.
    |
    | Supported: "admin" (string), ["admin", "vendor"] (array)
    |
    */

    'panels' => ['admin'],

    /*
    |--------------------------------------------------------------------------
    | Navigation Group
    |--------------------------------------------------------------------------
    |
    | This value determines the sidebar navigation group that the Products
    | cluster is nested under. You may provide a translation key or a literal
    | label, allowing you to file the product UI alongside a host application's
    | own groups. When left empty, the module's default group is used.
    |
    */

    'navigation_group' => 'vendra-product::navigation.content_management',

    /*
    |--------------------------------------------------------------------------
    | Product Token Generator
    |--------------------------------------------------------------------------
    |
    | Configure token generation for products. A token is built by drawing
    | `token_generator_length` characters from `token_generator_characters`
    | using a cryptographically secure random source (random_int).
    |
    */

    'token_generator_characters' => env('VENDRA_PRODUCT_TOKEN_GENERATOR_CHARACTERS', '123456789'),

    'token_generator_length' => (int) env('VENDRA_PRODUCT_TOKEN_GENERATOR_LENGTH', 9),
];
