<?php

namespace Async\Test\Task;


class CallableTaskTest extends \PHPUnit_Framework_TestCase {
    /**
     * Test that the task becomes complete after the callable object runs and that
     * the return value becomes the task result
     */
    public function testTaskResultIsReturnValue() {
        // Create a callable that expects to be called once and returns a known
        // value
        $callable = $this->getMock(\Async\Test\CallableStub::class);
        $callable->expects($this->once())->method('__invoke')
                                         ->will($this->returnValue("returned"));
        
        $task = new \Async\Task\CallableTask($callable);
        
        // Check that the task is not complete yet
        $this->assertFalse($task->isComplete());
        
        // Run the task
        $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
        
        // Check that the task is complete without error and the result is the
        // expected value
        $this->assertTrue($task->isComplete());
        $this->assertFalse($task->isFaulted());
        $this->assertEquals("returned", $task->getResult());
    }
    
    /**
     * Test that the task becomes complete after the callable object runs and that
     * the thrown exception becomes the task error
     */
    public function testTaskErrorIsThrownException() {
        // Create a callable that expects to be called once and throws an exception
        $callable = $this->getMock(\Async\Test\CallableStub::class);
        $callable->expects($this->once())->method('__invoke')
                                         ->will($this->throwException(new \Exception("thrown")));
        
        $task = new \Async\Task\CallableTask($callable);
        
        // Check that the task is not complete yet
        $this->assertFalse($task->isComplete());
        
        // Run the task
        $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
        
        // Check that the task is complete with the thrown error
        $this->assertTrue($task->isComplete());
        $this->assertTrue($task->isFaulted());
        $this->assertEquals("thrown", $task->getException()->getMessage());
    }
    
    /**
     * Test that the callable is only executed once even if the task is ticked
     * multiple times
     */
    public function testCallableExecutedOnlyOnce() {
        // Create a callable that expects to be called once
        $callable = $this->getMock(\Async\Test\CallableStub::class);
        $callable->expects($this->once())->method('__invoke');
        
        $task = new \Async\Task\CallableTask($callable);
        
        // Run the task twice
        $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
        $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
    }
}
