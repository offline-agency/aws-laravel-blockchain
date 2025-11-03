<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default blockchain driver that will be used
    | by the framework. You may set this to any of the drivers defined
    | in the "drivers" array below.
    |
    */
    'default_driver' => env('BLOCKCHAIN_DRIVER', 'mock'),

    /*
    |--------------------------------------------------------------------------
    | Public Driver
    |--------------------------------------------------------------------------
    |
    | The driver used for public blockchain operations (transparent data)
    |
    */
    'public_driver' => env('BLOCKCHAIN_PUBLIC_DRIVER', 'mock'),

    /*
    |--------------------------------------------------------------------------
    | Private Driver
    |--------------------------------------------------------------------------
    |
    | The driver used for private blockchain operations (confidential data)
    |
    */
    'private_driver' => env('BLOCKCHAIN_PRIVATE_DRIVER', 'mock'),

    /*
    |--------------------------------------------------------------------------
    | Blockchain Drivers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the blockchain drivers for your application.
    | You may add additional drivers as needed.
    |
    */
    'drivers' => [
        'mock' => [
            'type' => 'mock',
        ],

        'managed_blockchain' => [
            'type' => 'managed_blockchain',
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'access_key_id' => env('AWS_ACCESS_KEY_ID'),
            'secret_access_key' => env('AWS_SECRET_ACCESS_KEY'),
            'network_id' => env('AWS_BLOCKCHAIN_NETWORK_ID'),
            'member_id' => env('AWS_BLOCKCHAIN_MEMBER_ID'),
            'node_id' => env('AWS_BLOCKCHAIN_NODE_ID'),
            'channel_name' => env('BLOCKCHAIN_CHANNEL_NAME', 'mychannel'),
            'chaincode_name' => env('BLOCKCHAIN_CHAINCODE_NAME', 'supply-chain'),
        ],

        'qldb' => [
            'type' => 'qldb',
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'access_key_id' => env('AWS_ACCESS_KEY_ID'),
            'secret_access_key' => env('AWS_SECRET_ACCESS_KEY'),
            'ledger_name' => env('AWS_QLDB_LEDGER_NAME', 'supply-chain-ledger'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Categories
    |--------------------------------------------------------------------------
    |
    | Define which data categories are considered public vs private
    |
    */
    'data_categories' => [
        'public' => [
            'product_origin',
            'certifications',
            'public_timestamps',
            'quality_scores',
        ],
        'private' => [
            'supplier_details',
            'pricing',
            'internal_notes',
            'sensitive_locations',
        ],
    ],
];
