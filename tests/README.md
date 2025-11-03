# Testing Guide

This directory contains comprehensive tests for the AWS Blockchain Laravel package.

## Test Structure

```
tests/
├── Unit/                    # Unit tests for individual components
│   ├── Drivers/            # Driver-specific tests
│   └── BlockchainManagerTest.php
├── Feature/                # Feature tests for service integration
│   ├── ServiceProviderTest.php
│   └── ConfigurationTest.php
├── Integration/            # End-to-end integration tests
│   └── BlockchainIntegrationTest.php
├── TestCase.php           # Base test case with setup
├── Pest.php              # Pest configuration
└── README.md             # This file
```

## Running Tests

### Prerequisites

Make sure you have the required dependencies installed:

```bash
composer install
```

### Running All Tests

```bash
# Using Pest (recommended)
vendor/bin/pest

# Using PHPUnit
vendor/bin/phpunit

# With coverage
vendor/bin/pest --coverage
```

### Running Specific Test Suites

```bash
# Unit tests only
vendor/bin/pest tests/Unit

# Feature tests only
vendor/bin/pest tests/Feature

# Integration tests only
vendor/bin/pest tests/Integration

# Specific test file
vendor/bin/pest tests/Unit/Drivers/MockDriverTest.php
```

### Running with Coverage

```bash
# Generate HTML coverage report
vendor/bin/pest --coverage-html coverage

# Generate text coverage report
vendor/bin/pest --coverage-text
```

## Test Categories

### Unit Tests

- **MockDriverTest**: Tests the mock blockchain driver functionality
- **EnhancedMockDriverTest**: Tests advanced mock driver features
- **BlockchainManagerTest**: Tests the blockchain manager functionality

### Feature Tests

- **ServiceProviderTest**: Tests Laravel service provider integration
- **ConfigurationTest**: Tests configuration loading and validation

### Integration Tests

- **BlockchainIntegrationTest**: End-to-end blockchain operations testing

## Mock Services

The package includes comprehensive mock services for testing:

### MockDriver Features

- **Event Recording**: Simulate blockchain event recording
- **Event Retrieval**: Get recorded events by ID
- **Integrity Verification**: Verify event data integrity
- **Availability Testing**: Simulate driver availability
- **Network Simulation**: Simulate network delays and failures
- **Event Filtering**: Filter events by type or criteria
- **Event Counting**: Track number of recorded events

### Testing Scenarios

1. **Basic Operations**: Record, retrieve, and verify events
2. **Data Integrity**: Test hash generation and verification
3. **Error Handling**: Test with invalid data and missing events
4. **Performance**: Test with multiple events and operations
5. **Failure Simulation**: Test driver unavailability and network issues

## Test Configuration

### Environment Variables

Tests use the following environment variables:

```env
APP_ENV=testing
APP_KEY=base64:test-key-for-testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

### Test Database

Tests use an in-memory SQLite database for fast execution.

### Mock Configuration

Tests use mock drivers by default to avoid requiring AWS credentials.

## Continuous Integration

The package includes GitHub Actions workflows that test against:

- **PHP Versions**: 8.3, 8.4
- **Laravel Versions**: 11.*, 12.*
- **Test Types**: Unit, Feature, Integration
- **Code Quality**: PHPStan, Laravel Pint
- **Security**: Composer audit

## Writing New Tests

### Test Naming Convention

- Test methods should be descriptive: `test_can_record_event()`
- Use snake_case for test method names
- Group related tests in the same class

### Test Structure

```php
public function test_can_perform_operation()
{
    // Arrange
    $driver = new MockDriver('test');
    $data = ['test' => 'data'];
    
    // Act
    $result = $driver->recordEvent($data);
    
    // Assert
    $this->assertIsString($result);
    $this->assertTrue($driver->verifyIntegrity($result, $data));
}
```

### Best Practices

1. **Isolation**: Each test should be independent
2. **Clarity**: Use descriptive test names and comments
3. **Coverage**: Aim for high test coverage
4. **Mocking**: Use mock services for external dependencies
5. **Assertions**: Use specific assertions for better error messages

## Debugging Tests

### Verbose Output

```bash
vendor/bin/pest --verbose
```

### Stop on Failure

```bash
vendor/bin/pest --stop-on-failure
```

### Filter Tests

```bash
vendor/bin/pest --filter="test_can_record"
```

## Coverage Requirements

- **Minimum Coverage**: 80%
- **Target Coverage**: 90%+
- **Excluded**: Contracts, Service Provider, Test files

## Performance Testing

The mock driver includes performance testing capabilities:

```php
// Test network delay simulation
$driver->simulateNetworkDelay(100); // 100ms delay

// Test with multiple events
for ($i = 0; $i < 1000; $i++) {
    $driver->recordEvent(['id' => $i]);
}
```

## Troubleshooting

### Common Issues

1. **Memory Issues**: Use `--memory-limit=2G` for large test suites
2. **Timeout Issues**: Increase timeout for slow tests
3. **Coverage Issues**: Ensure Xdebug is installed and enabled

### Debug Commands

```bash
# Check PHP version
php --version

# Check Xdebug
php -m | grep xdebug

# Check memory limit
php -i | grep memory_limit
```
