<?php

return [
    'esg_bridge' => [
        'base_url' => env('BUMA_ESG_BASE_URL'),
        'api_key'  => env('BUMA_ESG_API_KEY'),
        'version'  => env('BUMA_ESG_VERSION', 'v1'),
    ],
    's3_agile_poc' => [
        'driver' => 's3',
        'key' => env('AWS_AGILE_POC_ACCESS_KEY_ID'),
        'secret' => env('AWS_AGILE_POC_SECRET_ACCESS_KEY'),
        'region' => env('AWS_AGILE_POC_DEFAULT_REGION', 'us-east-1'),
        'bucket' => env('AWS_AGILE_POC_BUCKET'),
        'use_path_style_endpoint' => env('AWS_AGILE_POC_USE_PATH_STYLE_ENDPOINT', false),
        'throw' => true,
    ],
];