<?php

namespace Async\Test\Scheduler;


class ReactEventLoopSchedulerTest extends AbstractSchedulerTest {
    /**
     * The React event loop being used
     *
     * @var \React\EventLoop\LoopInterface
     */
    protected $loop = null;
    
    public function setUp() {
        $this->loop = \React\EventLoop\Factory::create();
        $this->scheduler = new \Async\Scheduler\ReactEventLoopScheduler($this->loop);
    }
    
    /**
     * Test that a task scheduled with a delay is executed after that delay has
     * passed
     */
    public function testTaskScheduledWithDelayIsExecutedAfterDelay() {
        // Create a task that expects to be called once before completing
        $task = $this->getMock(TickCountingTask::class, ['doTick'], [1]);
        $task->expects($this->once())->method('doTick')->with($this->scheduler);
        
        // Schedule it with a delay
        $this->scheduler->schedule($task, 0.005);
        
        // Schedule a function at 0.004 to check that the task has not run
        $this->loop->addTimer(0.004, function() use($task) {
            $this->assertFalse($task->isComplete());
        });
        
        // Schedule a function at 0.006 to check that the task has run
        $this->loop->addTimer(0.006, function() use($task) {
            $this->assertTrue($task->isComplete());
        });
        
        $this->scheduler->run();
    }
    
    /**
     * Test that a task scheduled with a tickInterval has its tick method called
     * at that interval
     */
    public function testTaskScheduledWithTickIntervalIsInvokedPeriodicallyAtCorrectInterval() {
        // Create a task that expects to be called three times before completing
        $task = $this->getMock(TickCountingTask::class, ['doTick'], [3]);
        $task->expects($this->exactly(3))->method('doTick')->with($this->scheduler);
        
        // Schedule it with a tick interval
        $this->scheduler->schedule($task, null, 0.005);
        
        // The first tick should occur straight away, as we didn't give a delay to
        // start
        $this->loop->addTimer(0.002, function() use($task) {
            $this->assertFalse($task->isComplete());
            $this->assertSame(1, $task->getTickCount());
        });
        
        // Schedule a function at 0.004 to check that the task has not ticked again
        $this->loop->addTimer(0.004, function() use($task) {
            $this->assertFalse($task->isComplete());
            $this->assertSame(1, $task->getTickCount());
        });
        
        // Schedule a function at 0.007 to check that the task has ticked again
        $this->loop->addTimer(0.007, function() use($task) {
            $this->assertFalse($task->isComplete());
            $this->assertSame(2, $task->getTickCount());
        });
        
        // Schedule a function at 0.009 to check that the task has not ticked again
        $this->loop->addTimer(0.009, function() use($task) {
            $this->assertFalse($task->isComplete());
            $this->assertSame(2, $task->getTickCount());
        });
        
        // Schedule a function at 0.012 to check that the task has ticked again
        // and is now complete
        $this->loop->addTimer(0.012, function() use($task) {
            $this->assertTrue($task->isComplete());
            $this->assertSame(3, $task->getTickCount());
        });
        
        $this->scheduler->run();
    }
}