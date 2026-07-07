<?php

declare(strict_types=1);

return [

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

    'token_generator_repeat_count' => (int) env('VENDRA_PRODUCT_TOKEN_GENERATOR_REPEAT_COUNT', 9),

    'token_generator_length' => (int) env('VENDRA_PRODUCT_TOKEN_GENERATOR_LENGTH', 9),
];
