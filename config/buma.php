<?php

return [
    'esg_bridge' => [
        'base_url' => 'https://digital-dev-apm-001.azure-api.net/shared-external-bridge',
        'api_key'  => '870b511b5cfe44b8bfb13a8b0cc0bd19',
        'version'  => 'v1',
    ],
    's3_agile_poc' => [
        'driver' => 's3',
        'key' => env('AWS_AGILE_POC_ACCESS_KEY_ID'),
        'secret' => env('AWS_AGILE_POC_SECRET_ACCESS_KEY'),
        'region' => env('AWS_AGILE_POC_DEFAULT_REGION', 'us-east-1'),
        'bucket' => env('AWS_AGILE_POC_BUCKET'),
        'use_path_style_endpoint' => env('AWS_AGILE_POC_USE_PATH_STYLE_ENDPOINT', false), 
        'throw' => true, // makes errors visible in your command output
    ],
];
