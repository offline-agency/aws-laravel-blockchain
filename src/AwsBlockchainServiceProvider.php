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

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'aws-blockchain-laravel-migrations');

        // Register Artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\DeployContractCommand::class,
                Console\Commands\UpgradeContractCommand::class,
                Console\Commands\CallContractCommand::class,
                Console\Commands\TestContractCommand::class,
                Console\Commands\CompileContractCommand::class,
                Console\Commands\ListContractsCommand::class,
                Console\Commands\ContractStatusCommand::class,
                Console\Commands\VerifyContractCommand::class,
                Console\Commands\RollbackContractCommand::class,
                Console\Commands\WatchContractsCommand::class,
            ]);
        }
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
