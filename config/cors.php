<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    
    'allowed_methods' => ['*'],
    
    // 'allowed_origins' => [
    //     'https://www.wgrcfp.org',
    //     'https://wgrcfp.org',
    // ],
    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'https://localhost:3000',
        'https://127.0.0.1:3000',
        // 'https://www.wgrcfp.org',
        // 'https://wgrcfp.org',
        'http://localhost:8000',
        'http://127.0.0.1:8000',
    ],
    
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => ['*'],
     
    'exposed_headers' => [],
    
    'max_age' => 0,
    
    'supports_credentials' => false,
];