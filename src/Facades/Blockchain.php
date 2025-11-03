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
}
