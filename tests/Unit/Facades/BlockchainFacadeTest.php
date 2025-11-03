<?php

namespace AwsBlockchain\Laravel\Tests\Unit\Facades;

use AwsBlockchain\Laravel\BlockchainManager;
use AwsBlockchain\Laravel\Facades\Blockchain;
use AwsBlockchain\Laravel\Tests\TestCase;

class BlockchainFacadeTest extends TestCase
{
    public function test_facade_extends_correct_base_class()
    {
        $this->assertInstanceOf(\Illuminate\Support\Facades\Facade::class, new Blockchain);
    }

    public function test_facade_has_correct_accessor()
    {
        $reflection = new \ReflectionClass(Blockchain::class);
        $method = $reflection->getMethod('getFacadeAccessor');
        $method->setAccessible(true);

        $accessor = $method->invoke(null);

        $this->assertEquals('blockchain', $accessor);
    }

    public function test_facade_can_access_public_driver()
    {
        $driver = Blockchain::publicDriver();

        $this->assertNotNull($driver);
        $this->assertTrue(method_exists($driver, 'recordEvent'));
        $this->assertTrue(method_exists($driver, 'getEvent'));
        $this->assertTrue(method_exists($driver, 'verifyIntegrity'));
    }

    public function test_facade_can_access_private_driver()
    {
        $driver = Blockchain::privateDriver();

        $this->assertNotNull($driver);
        $this->assertTrue(method_exists($driver, 'recordEvent'));
        $this->assertTrue(method_exists($driver, 'getEvent'));
        $this->assertTrue(method_exists($driver, 'verifyIntegrity'));
    }

    public function test_facade_can_access_manager_methods()
    {
        $manager = Blockchain::getFacadeRoot();

        $this->assertInstanceOf(BlockchainManager::class, $manager);

        // Test manager methods
        $this->assertIsString($manager->getDefaultDriver());
        $this->assertIsArray($manager->getAvailableDrivers());
    }

    public function test_facade_can_switch_drivers()
    {
        $originalDriver = Blockchain::driver();

        Blockchain::setDefaultDriver('mock');
        $newDriver = Blockchain::driver();

        $this->assertNotNull($newDriver);
        $this->assertEquals('mock', $newDriver->getType());
    }

    public function test_facade_handles_static_calls()
    {
        // Test that static calls work
        $this->assertIsString(Blockchain::getDefaultDriver());
        $this->assertIsArray(Blockchain::getAvailableDrivers());
    }

    public function test_facade_throws_exception_for_undefined_method()
    {
        $this->expectException(\BadMethodCallException::class);

        Blockchain::undefinedMethod();
    }

    public function test_facade_works_with_different_drivers()
    {
        $driver1 = Blockchain::driver('mock');
        $driver2 = Blockchain::driver('mock');

        // Should return the same instance (cached)
        $this->assertSame($driver1, $driver2);
    }

    public function test_facade_preserves_manager_state()
    {
        $originalDefault = Blockchain::getDefaultDriver();

        Blockchain::setDefaultDriver('mock');
        $this->assertEquals('mock', Blockchain::getDefaultDriver());

        Blockchain::setDefaultDriver($originalDefault);
        $this->assertEquals($originalDefault, Blockchain::getDefaultDriver());
    }

    public function test_facade_works_with_all_manager_methods()
    {
        // Test all manager methods through facade
        $this->assertIsString(Blockchain::getDefaultDriver());
        $this->assertIsArray(Blockchain::getAvailableDrivers());

        // Test driver method
        $driver = Blockchain::driver();
        $this->assertNotNull($driver);

        // Test public and private drivers
        $publicDriver = Blockchain::publicDriver();
        $privateDriver = Blockchain::privateDriver();

        $this->assertNotNull($publicDriver);
        $this->assertNotNull($privateDriver);
    }
}
