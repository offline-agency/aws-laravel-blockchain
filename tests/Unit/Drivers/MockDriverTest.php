<?php

namespace AwsBlockchain\Laravel\Tests\Unit\Drivers;

use AwsBlockchain\Laravel\Drivers\MockDriver;
use AwsBlockchain\Laravel\Tests\TestCase;

class MockDriverTest extends TestCase
{
    public function test_can_create_mock_driver()
    {
        $driver = new MockDriver('test-mock');

        $this->assertInstanceOf(MockDriver::class, $driver);
        $this->assertEquals('test-mock', $driver->getType());
        $this->assertTrue($driver->isAvailable());
    }

    public function test_can_record_event()
    {
        $driver = new MockDriver('test-mock');
        $data = ['test' => 'data', 'timestamp' => now()->toIso8601String()];

        $eventId = $driver->recordEvent($data);

        $this->assertIsString($eventId);
        $this->assertStringStartsWith('mock_test-mock_', $eventId);
    }

    public function test_can_get_event()
    {
        $driver = new MockDriver('test-mock');
        $data = ['test' => 'data', 'timestamp' => now()->toIso8601String()];

        $eventId = $driver->recordEvent($data);
        $retrievedEvent = $driver->getEvent($eventId);

        $this->assertIsArray($retrievedEvent);
        $this->assertEquals($eventId, $retrievedEvent['id']);
        $this->assertEquals($data, $retrievedEvent['data']);
        $this->assertArrayHasKey('timestamp', $retrievedEvent);
        $this->assertArrayHasKey('hash', $retrievedEvent);
    }

    public function test_can_verify_integrity()
    {
        $driver = new MockDriver('test-mock');
        $data = ['test' => 'data', 'timestamp' => now()->toIso8601String()];

        $eventId = $driver->recordEvent($data);

        $this->assertTrue($driver->verifyIntegrity($eventId, $data));
        $this->assertFalse($driver->verifyIntegrity($eventId, ['different' => 'data']));
    }

    public function test_returns_null_for_nonexistent_event()
    {
        $driver = new MockDriver('test-mock');

        $this->assertNull($driver->getEvent('nonexistent-id'));
    }

    public function test_can_get_driver_info()
    {
        $driver = new MockDriver('test-mock');
        $info = $driver->getDriverInfo();

        $this->assertIsArray($info);
        $this->assertEquals('test-mock', $info['type']);
        $this->assertTrue($info['available']);
        $this->assertEquals('MockDriver', $info['driver']);
        $this->assertArrayHasKey('events_count', $info);
    }

    public function test_can_get_all_events()
    {
        $driver = new MockDriver('test-mock');

        $this->assertEmpty($driver->getAllEvents());

        $driver->recordEvent(['test' => 'data1']);
        $driver->recordEvent(['test' => 'data2']);

        $events = $driver->getAllEvents();
        $this->assertCount(2, $events);
    }

    public function test_can_clear_events()
    {
        $driver = new MockDriver('test-mock');

        $driver->recordEvent(['test' => 'data']);
        $this->assertCount(1, $driver->getAllEvents());

        $driver->clearEvents();
        $this->assertEmpty($driver->getAllEvents());
    }

    public function test_hash_generation_is_consistent()
    {
        $driver = new MockDriver('test-mock');
        $data = ['test' => 'data'];

        $eventId1 = $driver->recordEvent($data);
        $eventId2 = $driver->recordEvent($data);

        // Different IDs but same hash for same data
        $this->assertNotEquals($eventId1, $eventId2);

        $event1 = $driver->getEvent($eventId1);
        $event2 = $driver->getEvent($eventId2);

        $this->assertEquals($event1['hash'], $event2['hash']);
    }
}
