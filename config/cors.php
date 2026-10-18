<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    // Asegúrate de incluir la ruta de inicialización de Sanctum
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Reemplaza el comodín ['*'] por la URL exacta de tu frontend Vite
    'allowed_origins' => [
        'http://localhost:3000',
        'http://localhost:5173',
        env('FRONTEND_URL', 'http://localhost:3000'),
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Este valor debe ser obligatoriamente 'true' para aceptar cookies
    'supports_credentials' => true, 
];