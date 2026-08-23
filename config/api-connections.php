<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Configuration Lock
    |--------------------------------------------------------------------------
    |
    | When set to true, existing API configurations are locked and cannot be
    | modified, toggled, or deleted. New configurations cannot be added.
    |
    */
    'locked' => env('API_CONFIG_LOCKED', false),

    /*
    |--------------------------------------------------------------------------
    | Maximum API Connections
    |--------------------------------------------------------------------------
    |
    | This value determines the maximum number of API connections that can be
    | created within the application.
    |
    */
    'max_connections' => env('API_CONFIG_MAX_CONNECTIONS', 3),
];
