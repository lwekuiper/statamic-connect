<?php

return [
    'integrations' => [
        'activecampaign' => [
            'api_url' => env('ACTIVECAMPAIGN_API_URL'),
            'api_key' => env('ACTIVECAMPAIGN_API_KEY'),
        ],
        'hubspot' => [
            'access_token' => env('HUBSPOT_ACCESS_TOKEN'),
        ],
        'klaviyo' => [
            'api_key' => env('KLAVIYO_API_KEY'),
        ],
        'brevo' => [
            'api_key' => env('BREVO_API_KEY'),
        ],
        'salesforce' => [
            'instance_url' => env('SALESFORCE_INSTANCE_URL'),
            'client_id' => env('SALESFORCE_CLIENT_ID'),
            'client_secret' => env('SALESFORCE_CLIENT_SECRET'),
        ],
    ],
];
