<?php

namespace AwsBlockchain\Laravel\Facades;

use AwsBlockchain\Laravel\Contracts\BlockchainDriverInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static BlockchainDriverInterface publicDriver()
 * @method static BlockchainDriverInterface privateDriver()
 * @method static BlockchainDriverInterface driver(string $name = null)
 * @method static array getAvailableDrivers()
 * @method static void setDefaultDriver(string $name)
 * @method static string getDefaultDriver()
 */
class Blockchain extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'blockchain';
    }

    /**
     * Handle dynamic static method calls.
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::getFacadeRoot();

        if (! $instance) {
            throw new \BadMethodCallException(sprintf(
                'Call to undefined method %s::%s()',
                static::class,
                $method
            ));
        }

        if (! method_exists($instance, $method) && ! method_exists($instance, '__call')) {
            throw new \BadMethodCallException(sprintf(
                'Call to undefined method %s::%s()',
                static::class,
                $method
            ));
        }

        return $instance->$method(...$args);
    }
}
