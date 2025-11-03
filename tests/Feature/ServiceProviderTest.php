<?php

namespace AwsBlockchain\Laravel\Tests\Feature;

use AwsBlockchain\Laravel\Facades\Blockchain;
use AwsBlockchain\Laravel\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_blockchain_facade_is_registered()
    {
        $this->assertTrue(class_exists(Blockchain::class));
    }

    public function test_blockchain_manager_is_registered()
    {
        $manager = $this->app['blockchain'];

        $this->assertNotNull($manager);
        $this->assertInstanceOf(\AwsBlockchain\Laravel\BlockchainManager::class, $manager);
    }

    public function test_public_driver_is_registered()
    {
        $driver = $this->app['blockchain.public'];

        $this->assertNotNull($driver);
        $this->assertTrue(method_exists($driver, 'recordEvent'));
        $this->assertTrue(method_exists($driver, 'getEvent'));
        $this->assertTrue(method_exists($driver, 'verifyIntegrity'));
    }

    public function test_private_driver_is_registered()
    {
        $driver = $this->app['blockchain.private'];

        $this->assertNotNull($driver);
        $this->assertTrue(method_exists($driver, 'recordEvent'));
        $this->assertTrue(method_exists($driver, 'getEvent'));
        $this->assertTrue(method_exists($driver, 'verifyIntegrity'));
    }

    public function test_can_use_facade()
    {
        $publicDriver = Blockchain::publicDriver();
        $privateDriver = Blockchain::privateDriver();

        $this->assertNotNull($publicDriver);
        $this->assertNotNull($privateDriver);

        // Test that drivers work
        $data = ['test' => 'data'];
        $eventId = $publicDriver->recordEvent($data);

        $this->assertIsString($eventId);
        $this->assertTrue($publicDriver->verifyIntegrity($eventId, $data));
    }

    public function test_config_is_published()
    {
        $config = $this->app['config']['aws-blockchain-laravel'];

        $this->assertIsArray($config);
        $this->assertArrayHasKey('default_driver', $config);
        $this->assertArrayHasKey('public_driver', $config);
        $this->assertArrayHasKey('private_driver', $config);
        $this->assertArrayHasKey('drivers', $config);
        $this->assertArrayHasKey('data_categories', $config);
    }

    public function test_drivers_are_available()
    {
        $manager = $this->app['blockchain'];
        $drivers = $manager->getAvailableDrivers();

        $this->assertArrayHasKey('mock', $drivers);
        $this->assertTrue($drivers['mock']['available']);
    }
}
