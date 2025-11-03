<?php

namespace AwsBlockchain\Laravel\Tests\Feature;

use AwsBlockchain\Laravel\AwsBlockchainServiceProvider;
use AwsBlockchain\Laravel\Facades\Blockchain;
use AwsBlockchain\Laravel\Tests\TestCase;

class ServiceProviderComprehensiveTest extends TestCase
{
    public function test_service_provider_is_registered()
    {
        $providers = $this->app->getLoadedProviders();

        $this->assertArrayHasKey(AwsBlockchainServiceProvider::class, $providers);
        $this->assertTrue($providers[AwsBlockchainServiceProvider::class]);
    }

    public function test_all_services_are_registered()
    {
        $services = [
            'blockchain',
            'blockchain.public',
            'blockchain.private',
        ];

        foreach ($services as $service) {
            $this->assertTrue($this->app->bound($service), "Service {$service} is not bound");
            $this->assertNotNull($this->app->make($service), "Service {$service} cannot be resolved");
        }
    }

    public function test_services_are_singletons()
    {
        $blockchain1 = $this->app->make('blockchain');
        $blockchain2 = $this->app->make('blockchain');

        $this->assertSame($blockchain1, $blockchain2);
    }

    public function test_facade_works_correctly()
    {
        $this->assertTrue(class_exists(Blockchain::class));

        $publicDriver = Blockchain::publicDriver();
        $privateDriver = Blockchain::privateDriver();

        $this->assertNotNull($publicDriver);
        $this->assertNotNull($privateDriver);
    }

    public function test_config_is_loaded()
    {
        $config = $this->app['config']['aws-blockchain-laravel'];

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);
    }

    public function test_config_has_required_structure()
    {
        $config = $this->app['config']['aws-blockchain-laravel'];

        $requiredKeys = [
            'default_driver',
            'public_driver',
            'private_driver',
            'drivers',
            'data_categories',
        ];

        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $config, "Missing config key: {$key}");
        }
    }

    public function test_drivers_config_has_required_drivers()
    {
        $config = $this->app['config']['aws-blockchain-laravel'];
        $drivers = $config['drivers'];

        $requiredDrivers = ['mock'];

        foreach ($requiredDrivers as $driver) {
            $this->assertArrayHasKey($driver, $drivers, "Missing driver config: {$driver}");
            $this->assertArrayHasKey('type', $drivers[$driver], "Driver {$driver} missing type");
        }
    }

    public function test_data_categories_are_configured()
    {
        $config = $this->app['config']['aws-blockchain-laravel'];
        $categories = $config['data_categories'];

        $this->assertArrayHasKey('public', $categories);
        $this->assertArrayHasKey('private', $categories);

        $this->assertIsArray($categories['public']);
        $this->assertIsArray($categories['private']);

        $this->assertNotEmpty($categories['public']);
        $this->assertNotEmpty($categories['private']);
    }

    public function test_service_provider_provides_correct_services()
    {
        $provider = new AwsBlockchainServiceProvider($this->app);
        $provides = $provider->provides();

        $expectedServices = [
            'blockchain',
            'blockchain.public',
            'blockchain.private',
        ];

        foreach ($expectedServices as $service) {
            $this->assertContains($service, $provides, "Service {$service} not in provides()");
        }
    }

    public function test_config_publishing_works()
    {
        $this->assertFileExists(config_path('aws-blockchain-laravel.php'));

        $config = include config_path('aws-blockchain-laravel.php');
        $this->assertIsArray($config);
        $this->assertNotEmpty($config);
    }

    public function test_services_work_independently()
    {
        $publicDriver = $this->app->make('blockchain.public');
        $privateDriver = $this->app->make('blockchain.private');

        // Test that they work independently
        $publicData = ['test' => 'public'];
        $privateData = ['test' => 'private'];

        $publicEventId = $publicDriver->recordEvent($publicData);
        $privateEventId = $privateDriver->recordEvent($privateData);

        $this->assertIsString($publicEventId);
        $this->assertIsString($privateEventId);
        $this->assertNotEquals($publicEventId, $privateEventId);

        // Verify events are stored separately
        $this->assertNotNull($publicDriver->getEvent($publicEventId));
        $this->assertNotNull($privateDriver->getEvent($privateEventId));

        // Verify cross-contamination doesn't occur
        $this->assertNull($publicDriver->getEvent($privateEventId));
        $this->assertNull($privateDriver->getEvent($publicEventId));
    }

    public function test_services_handle_errors_gracefully()
    {
        $publicDriver = $this->app->make('blockchain.public');

        // Test with invalid data
        $this->assertIsString($publicDriver->recordEvent([]));

        // Test with null data
        $this->assertIsString($publicDriver->recordEvent(null));

        // Test with non-existent event
        $this->assertNull($publicDriver->getEvent('non-existent'));

        // Test verification of non-existent event
        $this->assertFalse($publicDriver->verifyIntegrity('non-existent', []));
    }

    public function test_services_are_available()
    {
        $publicDriver = $this->app->make('blockchain.public');
        $privateDriver = $this->app->make('blockchain.private');

        $this->assertTrue($publicDriver->isAvailable());
        $this->assertTrue($privateDriver->isAvailable());
    }

    public function test_driver_info_is_complete()
    {
        $publicDriver = $this->app->make('blockchain.public');
        $privateDriver = $this->app->make('blockchain.private');

        $publicInfo = $publicDriver->getDriverInfo();
        $privateInfo = $privateDriver->getDriverInfo();

        $requiredKeys = ['type', 'available', 'driver'];

        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $publicInfo, "Public driver info missing: {$key}");
            $this->assertArrayHasKey($key, $privateInfo, "Private driver info missing: {$key}");
        }
    }

    public function test_services_can_be_rebound()
    {
        $originalDriver = $this->app->make('blockchain.public');

        // Rebind the service
        $this->app->bind('blockchain.public', function () {
            return new \AwsBlockchain\Laravel\Drivers\MockDriver('rebound');
        });

        $newDriver = $this->app->make('blockchain.public');

        $this->assertNotSame($originalDriver, $newDriver);
        $this->assertEquals('rebound', $newDriver->getType());
    }
}
