<?php

namespace AwsBlockchain\Laravel;

use Illuminate\Support\ServiceProvider;

class AwsBlockchainServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('blockchain', function ($app) {
            $config = $app['config']['aws-blockchain-laravel'] ?? [];

            return new BlockchainManager($config);
        });

        $this->app->singleton('blockchain.public', function ($app) {
            $config = $app['config']['aws-blockchain-laravel'] ?? [];
            $manager = new BlockchainManager($config);

            return $manager->driver($config['public_driver'] ?? 'mock');
        });

        $this->app->singleton('blockchain.private', function ($app) {
            $config = $app['config']['aws-blockchain-laravel'] ?? [];
            $manager = new BlockchainManager($config);

            return $manager->driver($config['private_driver'] ?? 'mock');
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/aws-blockchain-laravel.php' => config_path('aws-blockchain-laravel.php'),
        ], 'aws-blockchain-laravel-config');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            'blockchain',
            'blockchain.public',
            'blockchain.private',
        ];
    }
}
