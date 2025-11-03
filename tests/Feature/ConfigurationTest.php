<?php

namespace AwsBlockchain\Laravel\Tests\Feature;

use AwsBlockchain\Laravel\Facades\Blockchain;
use AwsBlockchain\Laravel\Tests\TestCase;

class ConfigurationTest extends TestCase
{
    public function test_config_has_all_required_keys()
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
            $this->assertArrayHasKey($key, $config, "Config missing required key: {$key}");
        }
    }

    public function test_drivers_config_has_mock_driver()
    {
        $config = $this->app['config']['aws-blockchain-laravel'];

        $this->assertArrayHasKey('drivers', $config);
        $this->assertArrayHasKey('mock', $config['drivers']);
        $this->assertEquals('mock', $config['drivers']['mock']['type']);
    }

    public function test_data_categories_are_configured()
    {
        $config = $this->app['config']['aws-blockchain-laravel'];

        $this->assertArrayHasKey('data_categories', $config);
        $this->assertArrayHasKey('public', $config['data_categories']);
        $this->assertArrayHasKey('private', $config['data_categories']);

        $publicCategories = $config['data_categories']['public'];
        $privateCategories = $config['data_categories']['private'];

        $this->assertContains('product_origin', $publicCategories);
        $this->assertContains('certifications', $publicCategories);
        $this->assertContains('supplier_details', $privateCategories);
        $this->assertContains('pricing', $privateCategories);
    }

    public function test_can_switch_drivers_via_config()
    {
        // Test with mock driver
        $publicDriver = Blockchain::publicDriver();
        $this->assertEquals('mock', $publicDriver->getType());

        $privateDriver = Blockchain::privateDriver();
        $this->assertEquals('mock', $privateDriver->getType());
    }

    public function test_manager_respects_config_drivers()
    {
        $manager = $this->app['blockchain'];

        $availableDrivers = $manager->getAvailableDrivers();

        $this->assertArrayHasKey('mock', $availableDrivers);
        $this->assertTrue($availableDrivers['mock']['available']);
    }

    public function test_config_publishing_works()
    {
        // Publish the config file
        $this->artisan('vendor:publish', [
            '--provider' => 'AwsBlockchain\Laravel\AwsBlockchainServiceProvider',
            '--tag' => 'aws-blockchain-laravel-config',
        ]);

        $this->assertFileExists(config_path('aws-blockchain-laravel.php'));

        $config = include config_path('aws-blockchain-laravel.php');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('default_driver', $config);
    }

    public function test_environment_variables_are_respected()
    {
        // Test that environment variables can override config
        $this->app['config']->set('aws-blockchain-laravel.default_driver', 'test-driver');

        $manager = $this->app['blockchain'];
        $this->assertEquals('test-driver', $manager->getDefaultDriver());
    }

    public function test_driver_configuration_validation()
    {
        $config = $this->app['config']['aws-blockchain-laravel'];

        // Test mock driver config
        $mockConfig = $config['drivers']['mock'];
        $this->assertArrayHasKey('type', $mockConfig);
        $this->assertEquals('mock', $mockConfig['type']);

        // Test managed blockchain config structure
        if (isset($config['drivers']['managed_blockchain'])) {
            $mbConfig = $config['drivers']['managed_blockchain'];
            $requiredKeys = ['type', 'region', 'access_key_id', 'secret_access_key'];

            foreach ($requiredKeys as $key) {
                $this->assertArrayHasKey($key, $mbConfig, "Managed blockchain config missing: {$key}");
            }
        }

        // Test QLDB config structure
        if (isset($config['drivers']['qldb'])) {
            $qldbConfig = $config['drivers']['qldb'];
            $requiredKeys = ['type', 'region', 'access_key_id', 'secret_access_key', 'ledger_name'];

            foreach ($requiredKeys as $key) {
                $this->assertArrayHasKey($key, $qldbConfig, "QLDB config missing: {$key}");
            }
        }
    }
}
