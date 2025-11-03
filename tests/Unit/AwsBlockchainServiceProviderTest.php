<?php

namespace AwsBlockchain\Laravel\Tests\Unit;

use AwsBlockchain\Laravel\AwsBlockchainServiceProvider;
use AwsBlockchain\Laravel\Tests\TestCase;

class AwsBlockchainServiceProviderTest extends TestCase
{
    public function test_service_provider_can_be_instantiated()
    {
        $provider = new AwsBlockchainServiceProvider($this->app);

        $this->assertInstanceOf(AwsBlockchainServiceProvider::class, $provider);
    }

    public function test_register_method_registers_services()
    {
        $provider = new AwsBlockchainServiceProvider($this->app);

        // Test that services are not bound before register
        $this->assertFalse($this->app->bound('blockchain'));
        $this->assertFalse($this->app->bound('blockchain.public'));
        $this->assertFalse($this->app->bound('blockchain.private'));

        // Register the provider
        $provider->register();

        // Test that services are bound after register
        $this->assertTrue($this->app->bound('blockchain'));
        $this->assertTrue($this->app->bound('blockchain.public'));
        $this->assertTrue($this->app->bound('blockchain.private'));
    }

    public function test_register_method_binds_singletons()
    {
        $provider = new AwsBlockchainServiceProvider($this->app);
        $provider->register();

        // Test that services are singletons
        $blockchain1 = $this->app->make('blockchain');
        $blockchain2 = $this->app->make('blockchain');

        $this->assertSame($blockchain1, $blockchain2);
    }

    public function test_register_method_uses_config()
    {
        $this->app['config']->set('aws-blockchain-laravel', [
            'default_driver' => 'mock',
            'public_driver' => 'mock',
            'private_driver' => 'mock',
            'drivers' => [
                'mock' => ['type' => 'mock'],
            ],
        ]);

        $provider = new AwsBlockchainServiceProvider($this->app);
        $provider->register();

        $blockchain = $this->app->make('blockchain');
        $this->assertNotNull($blockchain);
    }

    public function test_boot_method_publishes_config()
    {
        $provider = new AwsBlockchainServiceProvider($this->app);

        // Mock the publish method
        $this->app->shouldReceive('publishes')
            ->once()
            ->with(
                \Mockery::on(function ($paths) {
                    return is_array($paths) &&
                           isset($paths[__DIR__.'/../../config/aws-blockchain-laravel.php']) &&
                           $paths[__DIR__.'/../../config/aws-blockchain-laravel.php'] === config_path('aws-blockchain-laravel.php');
                }),
                'aws-blockchain-laravel-config'
            );

        $provider->boot();
    }

    public function test_provides_method_returns_correct_services()
    {
        $provider = new AwsBlockchainServiceProvider($this->app);
        $provides = $provider->provides();

        $expectedServices = [
            'blockchain',
            'blockchain.public',
            'blockchain.private',
        ];

        $this->assertEquals($expectedServices, $provides);
    }

    public function test_provides_method_returns_array()
    {
        $provider = new AwsBlockchainServiceProvider($this->app);
        $provides = $provider->provides();

        $this->assertIsArray($provides);
        $this->assertNotEmpty($provides);
    }

    public function test_service_provider_implements_correct_interface()
    {
        $provider = new AwsBlockchainServiceProvider($this->app);

        $this->assertInstanceOf(\Illuminate\Support\ServiceProvider::class, $provider);
    }

    public function test_register_method_handles_missing_config()
    {
        // Remove config to test default behavior
        $this->app['config']->forget('aws-blockchain-laravel');

        $provider = new AwsBlockchainServiceProvider($this->app);

        // Should not throw exception
        $provider->register();

        // Services should still be bound
        $this->assertTrue($this->app->bound('blockchain'));
    }

    public function test_register_method_creates_correct_instances()
    {
        $provider = new AwsBlockchainServiceProvider($this->app);
        $provider->register();

        $blockchain = $this->app->make('blockchain');
        $publicDriver = $this->app->make('blockchain.public');
        $privateDriver = $this->app->make('blockchain.private');

        $this->assertInstanceOf(\AwsBlockchain\Laravel\BlockchainManager::class, $blockchain);
        $this->assertNotNull($publicDriver);
        $this->assertNotNull($privateDriver);
    }

    public function test_boot_method_can_be_called_multiple_times()
    {
        $provider = new AwsBlockchainServiceProvider($this->app);

        // Should not throw exception when called multiple times
        $provider->boot();
        $provider->boot();
        $provider->boot();

        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function test_register_method_can_be_called_multiple_times()
    {
        $provider = new AwsBlockchainServiceProvider($this->app);

        // Should not throw exception when called multiple times
        $provider->register();
        $provider->register();
        $provider->register();

        $this->assertTrue(true); // If we get here, no exception was thrown
    }
}
