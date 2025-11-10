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

    /*
    |--------------------------------------------------------------------------
    | Smart Contract Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for smart contract deployment and management
    |
    */
    'contracts' => [
        /*
        | Networks configuration for contract deployment
        */
        'networks' => [
            'mainnet' => [
                'type' => 'evm',
                'rpc_url' => env('BLOCKCHAIN_MAINNET_RPC', 'https://mainnet.infura.io/v3/YOUR-PROJECT-ID'),
                'chain_id' => 1,
                'explorer_url' => 'https://etherscan.io',
                'explorer_api_key' => env('ETHERSCAN_API_KEY'),
            ],
            'sepolia' => [
                'type' => 'evm',
                'rpc_url' => env('BLOCKCHAIN_SEPOLIA_RPC', 'https://sepolia.infura.io/v3/YOUR-PROJECT-ID'),
                'chain_id' => 11155111,
                'explorer_url' => 'https://sepolia.etherscan.io',
                'explorer_api_key' => env('ETHERSCAN_API_KEY'),
            ],
            'local' => [
                'type' => 'evm',
                'rpc_url' => env('BLOCKCHAIN_LOCAL_RPC', 'http://localhost:8545'),
                'chain_id' => 1337,
                'explorer_url' => null,
                'explorer_api_key' => null,
            ],
            'fabric' => [
                'type' => 'fabric',
                'network_id' => env('AWS_BLOCKCHAIN_NETWORK_ID'),
                'member_id' => env('AWS_BLOCKCHAIN_MEMBER_ID'),
                'node_id' => env('AWS_BLOCKCHAIN_NODE_ID'),
                'channel_name' => env('BLOCKCHAIN_CHANNEL_NAME', 'mychannel'),
            ],
        ],

        /*
        | Default network for deployments
        */
        'default_network' => env('BLOCKCHAIN_CONTRACT_NETWORK', 'local'),

        /*
        | Compiler settings
        */
        'compiler' => [
            'solc_path' => env('SOLC_PATH', 'solc'),
            'optimize' => env('SOLC_OPTIMIZE', true),
            'optimize_runs' => env('SOLC_OPTIMIZE_RUNS', 200),
            'evm_version' => env('SOLC_EVM_VERSION', 'paris'),
        ],

        /*
        | Storage paths for contract artifacts
        */
        'storage_path' => storage_path('app/contracts'),
        'sources_path' => base_path('contracts'),

        /*
        | Gas settings
        */
        'gas' => [
            'default_limit' => env('BLOCKCHAIN_GAS_LIMIT', 3000000),
            'price_multiplier' => env('BLOCKCHAIN_GAS_PRICE_MULTIPLIER', 1.1),
            'max_priority_fee' => env('BLOCKCHAIN_MAX_PRIORITY_FEE', 2000000000), // 2 gwei
            'max_fee_per_gas' => env('BLOCKCHAIN_MAX_FEE_PER_GAS', 100000000000), // 100 gwei
        ],

        /*
        | Deployment settings
        */
        'deployment' => [
            'auto_verify' => env('BLOCKCHAIN_AUTO_VERIFY', false),
            'confirmation_blocks' => env('BLOCKCHAIN_CONFIRMATION_BLOCKS', 2),
            'timeout' => env('BLOCKCHAIN_DEPLOYMENT_TIMEOUT', 300), // seconds
            'retry_attempts' => env('BLOCKCHAIN_RETRY_ATTEMPTS', 3),
        ],

        /*
        | Hot reload settings for development
        */
        'hot_reload' => [
            'enabled' => env('BLOCKCHAIN_HOT_RELOAD', false),
            'watch_paths' => [
                base_path('contracts'),
            ],
            'poll_interval' => 1000, // milliseconds
        ],
    ],
];
