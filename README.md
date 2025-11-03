# AWS Blockchain Laravel Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/aws-blockchain-laravel/aws-blockchain-laravel.svg?style=flat-square)](https://packagist.org/packages/aws-blockchain-laravel/aws-blockchain-laravel)

[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/aws-blockchain-laravel/aws-blockchain-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/aws-blockchain-laravel/aws-blockchain-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)

[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/aws-blockchain-laravel/aws-blockchain-laravel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/aws-blockchain-laravel/aws-blockchain-laravel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)

[![Total Downloads](https://img.shields.io/packagist/dt/aws-blockchain-laravel/aws-blockchain-laravel.svg?style=flat-square)](https://packagist.org/packages/aws-blockchain-laravel/aws-blockchain-laravel)

A comprehensive Laravel package for AWS blockchain integration, specifically designed for supply chain traceability applications.

## Features

- **Multiple Blockchain Drivers**: Support for AWS Managed Blockchain, Amazon QLDB, and Mock drivers
- **Dual Blockchain Architecture**: Separate public and private blockchain operations
- **Data Separation**: Automatic categorization of public vs private data
- **Laravel Integration**: Seamless integration with Laravel's service container
- **Testing Support**: Mock drivers for testing without AWS credentials

## Installation

This package can be included as a local package in your Laravel project. It's automatically loaded via Composer autoloading.

## Configuration

The package configuration is published to `config/aws-blockchain-laravel.php`:

```php
return [
    'default_driver' => env('BLOCKCHAIN_DRIVER', 'mock'),
    'public_driver' => env('BLOCKCHAIN_PUBLIC_DRIVER', 'mock'),
    'private_driver' => env('BLOCKCHAIN_PRIVATE_DRIVER', 'mock'),
    
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
];
```

## Usage

### Using the Facade

```php
use AwsBlockchain\Laravel\Facades\Blockchain;

// Get public driver
$publicDriver = Blockchain::publicDriver();

// Get private driver
$privateDriver = Blockchain::privateDriver();

// Record an event
$eventId = $publicDriver->recordEvent($data);

// Get an event
$event = $publicDriver->getEvent($eventId);

// Verify integrity
$isValid = $publicDriver->verifyIntegrity($eventId, $data);
```

### Using the Service Container

```php
// Get the blockchain manager
$manager = app('blockchain');

// Get specific drivers
$publicDriver = app('blockchain.public');
$privateDriver = app('blockchain.private');
```

## Drivers

### MockDriver

Perfect for testing and development. Stores events in memory and provides simulated blockchain operations.

```php
$driver = new \AwsBlockchain\Laravel\Drivers\MockDriver('mock');
```

### ManagedBlockchainDriver

Integrates with AWS Managed Blockchain service for production blockchain operations.

```php
$driver = new \AwsBlockchain\Laravel\Drivers\ManagedBlockchainDriver($config);
```

### QldbDriver

Uses Amazon QLDB for immutable, cryptographically verifiable transaction logs.

```php
$driver = new \AwsBlockchain\Laravel\Drivers\QldbDriver($config);
```

## Data Separation

The package automatically separates data into public and private categories:

**Public Data** (transparent blockchain):
- Product origin
- Certifications
- Public timestamps
- Quality scores

**Private Data** (confidential blockchain):
- Supplier details
- Pricing information
- Internal notes
- Sensitive locations

## Testing

The package includes comprehensive testing support with mock drivers that don't require AWS credentials:

```php
// In your tests
$this->app->bind('blockchain.public', function () {
    return new \AwsBlockchain\Laravel\Drivers\MockDriver('mock');
});
```

## Environment Variables

Add these to your `.env` file for production:

```env
# AWS Credentials
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1

# Blockchain Configuration
BLOCKCHAIN_DRIVER=mock
BLOCKCHAIN_PUBLIC_DRIVER=mock
BLOCKCHAIN_PRIVATE_DRIVER=mock

# For production with AWS
BLOCKCHAIN_PUBLIC_DRIVER=managed_blockchain
BLOCKCHAIN_PRIVATE_DRIVER=qldb

# AWS Blockchain Settings
AWS_BLOCKCHAIN_NETWORK_ID=your_network_id
AWS_BLOCKCHAIN_MEMBER_ID=your_member_id
AWS_BLOCKCHAIN_NODE_ID=your_node_id
BLOCKCHAIN_CHANNEL_NAME=mychannel
BLOCKCHAIN_CHAINCODE_NAME=supply-chain

# QLDB Settings
AWS_QLDB_LEDGER_NAME=supply-chain-ledger
```

## License

MIT License - see LICENSE file for details.
