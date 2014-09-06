<?php

namespace Async\Test\Task;


class RecurringTaskTest extends \PHPUnit_Framework_TestCase {
    /**
     * Test that the callable is executed correctly when the task is ticked
     */
    public function testCallableExecutedOnTick() {
        // Create a callable that expects to be called once
        $callable = $this->getMock(\Async\Test\CallableStub::class);
        $callable->expects($this->exactly(2))->method('__invoke');
        
        $task = new \Async\Task\RecurringTask($callable);
        
        // Check that the task is not complete
        $this->assertFalse($task->isComplete());
        
        // Tick the task - $callable should be called
        $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
        
        // Check that the task is still not complete
        $this->assertFalse($task->isComplete());
        
        // Tick the task again - $callable should be called again
        $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
        
        // Check that the task is still not complete
        $this->assertFalse($task->isComplete());
    }
    
    /**
     * Tests that the callable is not called and the task is instantly complete
     * when the task is asked to recur 0 times
     */
    public function testTaskCompleteWithZeroTimes() {
        // Create a callable that expects never to be called
        $callable = $this->getMock(\Async\Test\CallableStub::class);
        $callable->expects($this->never())->method('__invoke');
        
        $task = new \Async\Task\RecurringTask($callable, 0);
        
        // Check that the task is complete with no error, and running it doesn't
        // call the callable
        $this->assertTrue($task->isComplete());
        $this->assertFalse($task->isFaulted());
        $this->assertNull($task->getResult());
        
        $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
    }
    
    /**
     * Test that the callable is called the specified number of times when
     * $times >= 0
     */
    public function testTaskCompletesAfterCorrectNumberOfTimes() {
        // Create a callable that expects to be called 5 times
        $callable = $this->getMock(\Async\Test\CallableStub::class);
        $callable->expects($this->exactly(5))->method('__invoke');
        
        $task = new \Async\Task\RecurringTask($callable, 5);
        
        // Check 5 times that the task is not complete and tick the task
        for( $i = 0; $i < 5; $i++ ) {
            $this->assertFalse($task->isComplete());
            $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
        }
        
        // Check that the task is complete with no error, and running it doesn't
        // call the callable again
        $this->assertTrue($task->isComplete());
        $this->assertFalse($task->isFaulted());
        $this->assertNull($task->getResult());
        
        $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
    }
    
    /**
     * Test that an 'infinite' task will complete when an error is thrown
     */
    public function testTaskCompleteWhenExceptionThrown() {
        // Create a callable that expects to be called 5 times
        $callable = $this->getMock(\Async\Test\CallableStub::class);
        $callable->expects($this->exactly(5))->method('__invoke');
        // On the 5th call, throw an exception
        $callable->expects($this->at(4))->method('__invoke')
                         ->will($this->throwException(new \Exception("error")));
        
        $task = new \Async\Task\RecurringTask($callable);
        
        // Check 5 times that the task is not complete and tick the task
        for( $i = 0; $i < 5; $i++ ) {
            $this->assertFalse($task->isComplete());
            $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
        }
        
        // Check that the task is complete with the correct error
        $this->assertTrue($task->isComplete());
        $this->assertTrue($task->isFaulted());
        $this->assertEquals("error", $task->getException()->getMessage());
        
        // Check that ticking the task again doesn't invoke the callable again
        $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
    }
}