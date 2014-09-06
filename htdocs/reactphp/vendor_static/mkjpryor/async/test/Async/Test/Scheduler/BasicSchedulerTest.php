<?php

namespace Async\Test\Scheduler;


class BasicSchedulerTest extends AbstractSchedulerTest {
    public function setUp() {
        $this->scheduler = new \Async\Scheduler\BasicScheduler();
    }
    
    /**
     * Test that attempting to schedule a task with a delay results in an error
     * 
     * @expectedException \RuntimeException
     */
    public function testScheduleWithDelayIsError() {
        // Try to schedule a task with a delay
        $this->scheduler->schedule($this->getMock(\Async\Task\Task::class), 0.05);
    }
    
    /**
     * Test that attempting to schedule a task with a tickInterval results in an error
     * 
     * @expectedException \RuntimeException
     */
    public function testScheduleWithTickIntervalIsError() {
        // Try to schedule a task with a delay
        $this->scheduler->schedule($this->getMock(\Async\Task\Task::class), null, 0.05);
    }
}