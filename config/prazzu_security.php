<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Hardening de produção do Prazzu
    |--------------------------------------------------------------------------
    |
    | Centraliza proteções que não dependem de regra de negócio. Em produção,
    | mantenha as opções de debug desligadas e os headers de segurança ativos.
    |
    */

    'allow_debug_query_parameters' => env('PRAZZU_ALLOW_DEBUG_QUERY_PARAMETERS', false),

    'block_debug_query_parameters_in_production' => env('PRAZZU_BLOCK_DEBUG_QUERY_PARAMETERS_IN_PRODUCTION', true),

    'debug_query_parameters' => [
        'debug_sql',
        'debug_sql_all',
        'debug_performance',
        'xdebug',
        'phpinfo',
    ],

    'security_headers' => [
        'enabled' => env('PRAZZU_SECURITY_HEADERS_ENABLED', true),
        'hsts_enabled' => env('PRAZZU_HSTS_ENABLED', true),
        'hsts_max_age' => env('PRAZZU_HSTS_MAX_AGE', 31536000),
    ],
];
