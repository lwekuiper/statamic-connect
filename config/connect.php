<?php

return [

    'integrations' => [

        'activecampaign' => [
            'api_url' => env('CONNECT_ACTIVECAMPAIGN_API_URL'),
            'api_key' => env('CONNECT_ACTIVECAMPAIGN_API_KEY'),
        ],

        'mailchimp' => [
            'api_key' => env('CONNECT_MAILCHIMP_API_KEY'),
            'server' => env('CONNECT_MAILCHIMP_SERVER'),
        ],

        'hubspot' => [
            'access_token' => env('CONNECT_HUBSPOT_ACCESS_TOKEN'),
        ],

        'klaviyo' => [
            'api_key' => env('CONNECT_KLAVIYO_API_KEY'),
        ],

        'brevo' => [
            'api_key' => env('CONNECT_BREVO_API_KEY'),
        ],

        'salesforce' => [
            'client_id' => env('CONNECT_SALESFORCE_CLIENT_ID'),
            'client_secret' => env('CONNECT_SALESFORCE_CLIENT_SECRET'),
            'instance_url' => env('CONNECT_SALESFORCE_INSTANCE_URL'),
        ],

    ],

];
