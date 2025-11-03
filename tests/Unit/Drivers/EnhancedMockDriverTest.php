<?php

namespace AwsBlockchain\Laravel\Tests\Unit\Drivers;

use AwsBlockchain\Laravel\Drivers\MockDriver;
use AwsBlockchain\Laravel\Tests\TestCase;

class EnhancedMockDriverTest extends TestCase
{
    public function test_can_set_availability()
    {
        $driver = new MockDriver('test-mock');

        $this->assertTrue($driver->isAvailable());

        $driver->setAvailable(false);
        $this->assertFalse($driver->isAvailable());

        $driver->setAvailable(true);
        $this->assertTrue($driver->isAvailable());
    }

    public function test_can_get_events_by_type()
    {
        $driver = new MockDriver('test-mock');

        $driver->recordEvent(['event_type' => 'production', 'data' => 'test1']);
        $driver->recordEvent(['event_type' => 'shipping', 'data' => 'test2']);
        $driver->recordEvent(['event_type' => 'production', 'data' => 'test3']);

        $productionEvents = $driver->getEventsByType('production');
        $shippingEvents = $driver->getEventsByType('shipping');

        $this->assertCount(2, $productionEvents);
        $this->assertCount(1, $shippingEvents);
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
        $driver = new MockDriver('test-mock');

        $start = microtime(true);
        $driver->simulateNetworkDelay(10); // 10ms delay
        $end = microtime(true);

        $duration = ($end - $start) * 1000; // Convert to milliseconds
        $this->assertGreaterThanOrEqual(10, $duration);
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

    public function test_simulated_failure_affects_operations()
    {
        $driver = new MockDriver('test-mock');

        // Normal operation
        $eventId = $driver->recordEvent(['test' => 'data']);
        $this->assertIsString($eventId);

        // Simulate failure
        $driver->simulateFailure(true);

        // Operations should still work with mock driver (it doesn't actually fail)
        // but availability should be false
        $this->assertFalse($driver->isAvailable());
    }

    public function test_events_persistence_across_operations()
    {
        $driver = new MockDriver('test-mock');

        $events = [];
        for ($i = 1; $i <= 3; $i++) {
            $data = ['id' => $i, 'type' => 'test'];
            $eventId = $driver->recordEvent($data);
            $events[] = $eventId;
        }

        $this->assertEquals(3, $driver->getEventsCount());

        // Verify all events are still accessible
        foreach ($events as $eventId) {
            $event = $driver->getEvent($eventId);
            $this->assertNotNull($event);
            $this->assertEquals($eventId, $event['id']);
        }
    }

    public function test_driver_info_includes_events_count()
    {
        $driver = new MockDriver('test-mock');

        $info = $driver->getDriverInfo();
        $this->assertEquals(0, $info['events_count']);

        $driver->recordEvent(['test' => 'data']);

        $info = $driver->getDriverInfo();
        $this->assertEquals(1, $info['events_count']);
    }
}
