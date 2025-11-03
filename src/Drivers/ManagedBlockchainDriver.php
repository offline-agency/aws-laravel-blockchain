<?php

namespace AwsBlockchain\Laravel\Drivers;

use Aws\ManagedBlockchain\ManagedBlockchainClient;
use AwsBlockchain\Laravel\Contracts\BlockchainDriverInterface;
use Illuminate\Support\Facades\Log;

class ManagedBlockchainDriver implements BlockchainDriverInterface
{
    protected ManagedBlockchainClient $client;

    protected ?string $networkId;

    protected ?string $memberId;

    protected ?string $nodeId;

    protected string $channelName;

    protected string $chaincodeName;

    /**
     * @param  array<string, mixed>  $config
     * @param  ManagedBlockchainClient|null  $client
     */
    public function __construct(array $config, ?ManagedBlockchainClient $client = null)
    {
        $this->networkId = $config['network_id'] ?? null;
        $this->memberId = $config['member_id'] ?? null;
        $this->nodeId = $config['node_id'] ?? null;
        $this->channelName = $config['channel_name'] ?? 'mychannel';
        $this->chaincodeName = $config['chaincode_name'] ?? 'supply-chain';

        $this->client = $client ?? new ManagedBlockchainClient([
            'version' => 'latest',
            'region' => $config['region'] ?? 'us-east-1',
            'credentials' => [
                'key' => $config['access_key_id'],
                'secret' => $config['secret_access_key'],
            ],
        ]);
    }

    /**
     * Record an event on the blockchain
     */
    public function recordEvent(array $data): string
    {
        try {
            $transactionId = 'tx_'.uniqid().'_'.time();

            // In a real implementation, this would invoke a chaincode function
            // For now, we'll simulate the transaction
            $result = $this->client->invoke([
                'NetworkId' => $this->networkId,
                'MemberId' => $this->memberId,
                'NodeId' => $this->nodeId,
                'ChannelName' => $this->channelName,
                'ChaincodeName' => $this->chaincodeName,
                'Function' => 'recordEvent',
                'Arguments' => [
                    json_encode($data),
                    $transactionId,
                ],
            ]);

            Log::info('Event recorded on Managed Blockchain', [
                'transaction_id' => $transactionId,
                'data' => $data,
            ]);

            return $transactionId;
        } catch (\Exception $e) {
            Log::error('Failed to record event on Managed Blockchain', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Get an event from the blockchain
     */
    public function getEvent(string $id): ?array
    {
        try {
            $result = $this->client->invoke([
                'NetworkId' => $this->networkId,
                'MemberId' => $this->memberId,
                'NodeId' => $this->nodeId,
                'ChannelName' => $this->channelName,
                'ChaincodeName' => $this->chaincodeName,
                'Function' => 'getEvent',
                'Arguments' => [$id],
            ]);

            return json_decode($result['Payload'], true);
        } catch (\Exception $e) {
            Log::error('Failed to get event from Managed Blockchain', [
                'error' => $e->getMessage(),
                'event_id' => $id,
            ]);

            return null;
        }
    }

    /**
     * Verify event integrity
     */
    public function verifyIntegrity(string $id, array $data): bool
    {
        try {
            $result = $this->client->invoke([
                'NetworkId' => $this->networkId,
                'MemberId' => $this->memberId,
                'NodeId' => $this->nodeId,
                'ChannelName' => $this->channelName,
                'ChaincodeName' => $this->chaincodeName,
                'Function' => 'verifyIntegrity',
                'Arguments' => [
                    $id,
                    json_encode($data),
                ],
            ]);

            $response = json_decode($result['Payload'], true);

            return $response['verified'] ?? false;
        } catch (\Exception $e) {
            Log::error('Failed to verify event integrity on Managed Blockchain', [
                'error' => $e->getMessage(),
                'event_id' => $id,
            ]);

            return false;
        }
    }

    /**
     * Check if driver is available
     */
    public function isAvailable(): bool
    {
        try {
            $this->client->describeNetwork([
                'NetworkId' => $this->networkId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::warning('Managed Blockchain not available', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get driver type
     */
    public function getType(): string
    {
        return 'managed_blockchain';
    }

    /**
     * Get driver info
     */
    public function getDriverInfo(): array
    {
        return [
            'type' => $this->getType(),
            'available' => $this->isAvailable(),
            'network_id' => $this->networkId,
            'member_id' => $this->memberId,
            'channel_name' => $this->channelName,
            'chaincode_name' => $this->chaincodeName,
            'driver' => 'ManagedBlockchainDriver',
        ];
    }
}
