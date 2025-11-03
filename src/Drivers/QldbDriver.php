<?php

namespace AwsBlockchain\Laravel\Drivers;

use Aws\QLDB\QLDBClient;
use Aws\QLDBSession\QLDBSessionClient;
use AwsBlockchain\Laravel\Contracts\BlockchainDriverInterface;
use Illuminate\Support\Facades\Log;

class QldbDriver implements BlockchainDriverInterface
{
    protected QLDBClient $client;

    protected QLDBSessionClient $sessionClient;

    protected string $ledgerName;

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(array $config)
    {
        $this->ledgerName = $config['ledger_name'] ?? 'supply-chain-ledger';

        $this->client = new QLDBClient([
            'version' => 'latest',
            'region' => $config['region'] ?? 'us-east-1',
            'credentials' => [
                'key' => $config['access_key_id'],
                'secret' => $config['secret_access_key'],
            ],
        ]);

        $this->sessionClient = new QLDBSessionClient([
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
            $documentId = 'doc_'.uniqid().'_'.time();

            $result = $this->sessionClient->sendCommand([
                'SessionToken' => $this->getSessionToken(),
                'ExecuteStatement' => [
                    'TransactionId' => $this->startTransaction(),
                    'Statement' => "INSERT INTO SupplyChainEvents VALUE {'id': ?, 'data': ?, 'timestamp': ?, 'hash': ?}",
                    'Parameters' => [
                        ['StringValue' => $documentId],
                        ['StringValue' => json_encode($data)],
                        ['StringValue' => now()->toIso8601String()],
                        ['StringValue' => $this->generateHash($data)],
                    ],
                ],
            ]);

            Log::info('Event recorded on QLDB', [
                'document_id' => $documentId,
                'data' => $data,
            ]);

            return $documentId;
        } catch (\Exception $e) {
            Log::error('Failed to record event on QLDB', [
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
            $result = $this->sessionClient->sendCommand([
                'SessionToken' => $this->getSessionToken(),
                'ExecuteStatement' => [
                    'TransactionId' => $this->startTransaction(),
                    'Statement' => 'SELECT * FROM SupplyChainEvents WHERE id = ?',
                    'Parameters' => [
                        ['StringValue' => $id],
                    ],
                ],
            ]);

            $items = $result['ExecuteStatement']['FirstPage']['Values'] ?? [];
            if (empty($items)) {
                return null;
            }

            return json_decode($items[0]['Document']['data'], true);
        } catch (\Exception $e) {
            Log::error('Failed to get event from QLDB', [
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
            $result = $this->sessionClient->sendCommand([
                'SessionToken' => $this->getSessionToken(),
                'ExecuteStatement' => [
                    'TransactionId' => $this->startTransaction(),
                    'Statement' => 'SELECT hash FROM SupplyChainEvents WHERE id = ?',
                    'Parameters' => [
                        ['StringValue' => $id],
                    ],
                ],
            ]);

            $items = $result['ExecuteStatement']['FirstPage']['Values'] ?? [];
            if (empty($items)) {
                return false;
            }

            $storedHash = $items[0]['Document']['hash'];
            $expectedHash = $this->generateHash($data);

            return $storedHash === $expectedHash;
        } catch (\Exception $e) {
            Log::error('Failed to verify event integrity on QLDB', [
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
            $this->client->describeLedger([
                'Name' => $this->ledgerName,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::warning('QLDB not available', [
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
        return 'qldb';
    }

    /**
     * Get driver info
     */
    public function getDriverInfo(): array
    {
        return [
            'type' => $this->getType(),
            'available' => $this->isAvailable(),
            'ledger_name' => $this->ledgerName,
            'driver' => 'QldbDriver',
        ];
    }

    /**
     * Get session token for QLDB operations
     */
    protected function getSessionToken(): string
    {
        $result = $this->client->sendCommand([
            'StartSession' => [
                'LedgerName' => $this->ledgerName,
            ],
        ]);

        return $result['StartSession']['SessionToken'];
    }

    /**
     * Start a transaction
     */
    protected function startTransaction(): string
    {
        $result = $this->sessionClient->sendCommand([
            'SessionToken' => $this->getSessionToken(),
            'StartTransaction' => [],
        ]);

        return $result['StartTransaction']['TransactionId'];
    }

    /**
     * Generate hash for data integrity
     */
    /**
     * @param  array<string, mixed>  $data
     */
    protected function generateHash(array $data): string
    {
        return hash('sha256', json_encode($data).$this->ledgerName);
    }
}
