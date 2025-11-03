<?php

declare(strict_types=1);

namespace AwsBlockchain\Laravel\Contracts;

interface BlockchainDriverInterface
{
    /**
     * Record an event on the blockchain
     *
     * @param  array<string, mixed>  $data
     */
    public function recordEvent(array $data): string;

    /**
     * Get an event from the blockchain
     *
     * @return array<string, mixed>|null
     */
    public function getEvent(string $id): ?array;

    /**
     * Verify event integrity
     *
     * @param  array<string, mixed>  $data
     */
    public function verifyIntegrity(string $id, array $data): bool;

    /**
     * Check if driver is available
     */
    public function isAvailable(): bool;

    /**
     * Get driver type
     */
    public function getType(): string;

    /**
     * Get driver info
     *
     * @return array<string, mixed>
     */
    public function getDriverInfo(): array;
}
