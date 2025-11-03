<?php

namespace AwsBlockchain\Laravel\Tests\Unit\Drivers;

use Aws\Exception\AwsException;
use Aws\ManagedBlockchain\ManagedBlockchainClient;
use Aws\Result;
use AwsBlockchain\Laravel\Drivers\ManagedBlockchainDriver;
use AwsBlockchain\Laravel\Tests\TestCase;
use Mockery;

class ManagedBlockchainDriverTest extends TestCase
{
    protected $mockClient;

    protected $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = [
            'region' => 'us-east-1',
            'access_key_id' => 'test-key',
            'secret_access_key' => 'test-secret',
            'network_id' => 'test-network',
            'member_id' => 'test-member',
            'node_id' => 'test-node',
            'channel_name' => 'mychannel',
            'chaincode_name' => 'supply-chain',
        ];

        $this->mockClient = Mockery::mock(ManagedBlockchainClient::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_can_create_managed_blockchain_driver()
    {
        $driver = new ManagedBlockchainDriver($this->config);

        $this->assertInstanceOf(ManagedBlockchainDriver::class, $driver);
        $this->assertEquals('managed_blockchain', $driver->getType());
    }

    public function test_can_record_event_successfully()
    {
        $driver = new ManagedBlockchainDriver($this->config);

        // Mock the client to return a successful result
        $this->app->instance(ManagedBlockchainClient::class, $this->mockClient);

        $this->mockClient->shouldReceive('invoke')
            ->once()
            ->with(Mockery::on(function ($args) {
                return $args['NetworkId'] === 'test-network' &&
                       $args['MemberId'] === 'test-member' &&
                       $args['NodeId'] === 'test-node' &&
                       $args['ChannelName'] === 'mychannel' &&
                       $args['ChaincodeName'] === 'supply-chain' &&
                       $args['Function'] === 'recordEvent';
            }))
            ->andReturn(new Result(['TransactionId' => 'test-tx-id']));

        $data = ['test' => 'data'];
        $eventId = $driver->recordEvent($data);

        $this->assertIsString($eventId);
        $this->assertStringStartsWith('tx_', $eventId);
    }

    public function test_handles_record_event_exception()
    {
        $driver = new ManagedBlockchainDriver($this->config);

        $this->app->instance(ManagedBlockchainClient::class, $this->mockClient);

        $this->mockClient->shouldReceive('invoke')
            ->once()
            ->andThrow(new AwsException('Network error', new \Aws\Command('invoke')));

        $this->expectException(AwsException::class);

        $driver->recordEvent(['test' => 'data']);
    }

    public function test_can_get_event_successfully()
    {
        $driver = new ManagedBlockchainDriver($this->config);

        $this->app->instance(ManagedBlockchainClient::class, $this->mockClient);

        $expectedData = ['test' => 'data'];
        $this->mockClient->shouldReceive('invoke')
            ->once()
            ->with(Mockery::on(function ($args) {
                return $args['Function'] === 'getEvent' &&
                       $args['Arguments'][0] === 'test-event-id';
            }))
            ->andReturn(new Result(['Payload' => json_encode($expectedData)]));

        $result = $driver->getEvent('test-event-id');

        $this->assertEquals($expectedData, $result);
    }

    public function test_handles_get_event_exception()
    {
        $driver = new ManagedBlockchainDriver($this->config);

        $this->app->instance(ManagedBlockchainClient::class, $this->mockClient);

        $this->mockClient->shouldReceive('invoke')
            ->once()
            ->andThrow(new AwsException('Network error', new \Aws\Command('invoke')));

        $result = $driver->getEvent('test-event-id');

        $this->assertNull($result);
    }

    public function test_can_verify_integrity_successfully()
    {
        $driver = new ManagedBlockchainDriver($this->config);

        $this->app->instance(ManagedBlockchainClient::class, $this->mockClient);

        $this->mockClient->shouldReceive('invoke')
            ->once()
            ->with(Mockery::on(function ($args) {
                return $args['Function'] === 'verifyIntegrity' &&
                       $args['Arguments'][0] === 'test-event-id';
            }))
            ->andReturn(new Result(['Payload' => json_encode(['verified' => true])]));

        $result = $driver->verifyIntegrity('test-event-id', ['test' => 'data']);

        $this->assertTrue($result);
    }

    public function test_handles_verify_integrity_exception()
    {
        $driver = new ManagedBlockchainDriver($this->config);

        $this->app->instance(ManagedBlockchainClient::class, $this->mockClient);

        $this->mockClient->shouldReceive('invoke')
            ->once()
            ->andThrow(new AwsException('Network error', new \Aws\Command('invoke')));

        $result = $driver->verifyIntegrity('test-event-id', ['test' => 'data']);

        $this->assertFalse($result);
    }

    public function test_can_check_availability_successfully()
    {
        $driver = new ManagedBlockchainDriver($this->config);

        $this->app->instance(ManagedBlockchainClient::class, $this->mockClient);

        $this->mockClient->shouldReceive('describeNetwork')
            ->once()
            ->with(['NetworkId' => 'test-network'])
            ->andReturn(new Result(['Network' => ['Id' => 'test-network']]));

        $result = $driver->isAvailable();

        $this->assertTrue($result);
    }

    public function test_handles_availability_check_exception()
    {
        $driver = new ManagedBlockchainDriver($this->config);

        $this->app->instance(ManagedBlockchainClient::class, $this->mockClient);

        $this->mockClient->shouldReceive('describeNetwork')
            ->once()
            ->andThrow(new AwsException('Network error', new \Aws\Command('describeNetwork')));

        $result = $driver->isAvailable();

        $this->assertFalse($result);
    }

    public function test_can_get_driver_info()
    {
        $driver = new ManagedBlockchainDriver($this->config);

        $info = $driver->getDriverInfo();

        $this->assertIsArray($info);
        $this->assertEquals('managed_blockchain', $info['type']);
        $this->assertEquals('test-network', $info['network_id']);
        $this->assertEquals('test-member', $info['member_id']);
        $this->assertEquals('mychannel', $info['channel_name']);
        $this->assertEquals('supply-chain', $info['chaincode_name']);
        $this->assertEquals('ManagedBlockchainDriver', $info['driver']);
    }

    public function test_uses_default_config_values()
    {
        $minimalConfig = [
            'access_key_id' => 'test-key',
            'secret_access_key' => 'test-secret',
        ];

        $driver = new ManagedBlockchainDriver($minimalConfig);

        $info = $driver->getDriverInfo();

        $this->assertEquals('mychannel', $info['channel_name']);
        $this->assertEquals('supply-chain', $info['chaincode_name']);
    }
}
