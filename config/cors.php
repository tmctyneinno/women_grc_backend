<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    
    'allowed_methods' => ['*'],
    
    'allowed_origins' => [
        'https://www.wgrcfp.org',
        'https://wgrcfp.org',
    ],
    
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => ['*'],
     
    'exposed_headers' => [],
    
    'max_age' => 0,
    
    'supports_credentials' => false,
];