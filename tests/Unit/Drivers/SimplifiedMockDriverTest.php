<?php

namespace AwsBlockchain\Laravel\Tests\Unit\Drivers;

use AwsBlockchain\Laravel\Drivers\MockDriver;
use AwsBlockchain\Laravel\Tests\TestCase;

class SimplifiedMockDriverTest extends TestCase
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
        $data = ['test' => 'data'];

        $eventId = $driver->recordEvent($data);

        $this->assertIsString($eventId);
        $this->assertStringStartsWith('mock_test-mock_', $eventId);
    }

    public function test_can_record_empty_event()
    {
        $driver = new MockDriver('test-mock');

        $eventId = $driver->recordEvent([]);

        $this->assertIsString($eventId);
    }

    public function test_can_get_event()
    {
        $driver = new MockDriver('test-mock');
        $data = ['test' => 'data'];

        $eventId = $driver->recordEvent($data);
        $retrievedEvent = $driver->getEvent($eventId);

        $this->assertIsArray($retrievedEvent);
        $this->assertEquals($eventId, $retrievedEvent['id']);
        $this->assertEquals($data, $retrievedEvent['data']);
    }

    public function test_returns_null_for_nonexistent_event()
    {
        $driver = new MockDriver('test-mock');

        $this->assertNull($driver->getEvent('nonexistent-id'));
    }

    public function test_can_verify_integrity()
    {
        $driver = new MockDriver('test-mock');
        $data = ['test' => 'data'];

        $eventId = $driver->recordEvent($data);

        $this->assertTrue($driver->verifyIntegrity($eventId, $data));
        $this->assertFalse($driver->verifyIntegrity($eventId, ['different' => 'data']));
    }

    public function test_can_get_driver_info()
    {
        $driver = new MockDriver('test-mock');
        $info = $driver->getDriverInfo();

        $this->assertIsArray($info);
        $this->assertEquals('test-mock', $info['type']);
        $this->assertTrue($info['available']);
        $this->assertEquals('MockDriver', $info['driver']);
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

    public function test_can_set_availability()
    {
        $driver = new MockDriver('test-mock');

        $this->assertTrue($driver->isAvailable());

        $driver->setAvailable(false);
        $this->assertFalse($driver->isAvailable());

        $driver->setAvailable(true);
        $this->assertTrue($driver->isAvailable());
    }

    public function test_can_get_events_count()
    {
        $driver = new MockDriver('test-mock');

        $this->assertEquals(0, $driver->getEventsCount());

        $driver->recordEvent(['test' => 'data1']);
        $driver->recordEvent(['test' => 'data2']);

        $this->assertEquals(2, $driver->getEventsCount());
    }

    public function test_can_simulate_network_delay()
    {
        $driver = new MockDriver('test');

        $start = microtime(true);
        $driver->simulateNetworkDelay(1); // 1ms delay
        $end = microtime(true);

        $duration = ($end - $start) * 1000; // Convert to milliseconds
        $this->assertGreaterThanOrEqual(1, $duration);
    }

    public function test_can_simulate_failure()
    {
        $driver = new MockDriver('test-mock');

        $this->assertTrue($driver->isAvailable());

        $driver->simulateFailure(true);
        $this->assertFalse($driver->isAvailable());

        $driver->simulateFailure(false);
        $this->assertTrue($driver->isAvailable());
    }
}
