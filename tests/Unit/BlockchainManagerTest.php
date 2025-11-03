<?php

namespace AwsBlockchain\Laravel\Tests\Unit;

use AwsBlockchain\Laravel\BlockchainManager;
use AwsBlockchain\Laravel\Tests\TestCase;

class BlockchainManagerTest extends TestCase
{
    public function test_can_create_manager_with_config()
    {
        $config = [
            'default_driver' => 'mock',
            'public_driver' => 'mock',
            'private_driver' => 'mock',
            'drivers' => [
                'mock' => ['type' => 'mock'],
            ],
        ];

        $manager = new BlockchainManager($config);

        $this->assertInstanceOf(BlockchainManager::class, $manager);
        $this->assertEquals('mock', $manager->getDefaultDriver());
    }

    public function test_can_get_driver()
    {
        $config = [
            'default_driver' => 'mock',
            'drivers' => [
                'mock' => ['type' => 'mock'],
            ],
        ];

        $manager = new BlockchainManager($config);
        $driver = $manager->driver();

        $this->assertNotNull($driver);
        $this->assertEquals('mock', $driver->getType());
    }

    public function test_can_get_specific_driver()
    {
        $config = [
            'default_driver' => 'mock',
            'drivers' => [
                'mock' => ['type' => 'mock'],
            ],
        ];

        $manager = new BlockchainManager($config);
        $driver = $manager->driver('mock');

        $this->assertNotNull($driver);
        $this->assertEquals('mock', $driver->getType());
    }

    public function test_can_get_public_driver()
    {
        $config = [
            'default_driver' => 'mock',
            'public_driver' => 'mock',
            'drivers' => [
                'mock' => ['type' => 'mock'],
            ],
        ];

        $manager = new BlockchainManager($config);
        $driver = $manager->publicDriver();

        $this->assertNotNull($driver);
        $this->assertEquals('mock', $driver->getType());
    }

    public function test_can_get_private_driver()
    {
        $config = [
            'default_driver' => 'mock',
            'private_driver' => 'mock',
            'drivers' => [
                'mock' => ['type' => 'mock'],
            ],
        ];

        $manager = new BlockchainManager($config);
        $driver = $manager->privateDriver();

        $this->assertNotNull($driver);
        $this->assertEquals('mock', $driver->getType());
    }

    public function test_can_set_default_driver()
    {
        $config = [
            'default_driver' => 'mock',
            'drivers' => [
                'mock' => ['type' => 'mock'],
            ],
        ];

        $manager = new BlockchainManager($config);
        $manager->setDefaultDriver('mock');

        $this->assertEquals('mock', $manager->getDefaultDriver());
    }

    public function test_can_get_available_drivers()
    {
        $config = [
            'default_driver' => 'mock',
            'drivers' => [
                'mock' => ['type' => 'mock'],
            ],
        ];

        $manager = new BlockchainManager($config);
        $drivers = $manager->getAvailableDrivers();

        $this->assertIsArray($drivers);
        $this->assertArrayHasKey('mock', $drivers);
        $this->assertEquals('mock', $drivers['mock']['type']);
        $this->assertTrue($drivers['mock']['available']);
    }

    public function test_driver_caching()
    {
        $config = [
            'default_driver' => 'mock',
            'drivers' => [
                'mock' => ['type' => 'mock'],
            ],
        ];

        $manager = new BlockchainManager($config);
        $driver1 = $manager->driver();
        $driver2 = $manager->driver();

        // Should return the same instance (cached)
        $this->assertSame($driver1, $driver2);
    }
}
