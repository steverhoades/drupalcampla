<?php

namespace Async\Test\Util;


class AsyncTest extends \PHPUnit_Framework_TestCase {
    /**
     * Test that Util::async returns the given task when given a task
     */
    public function testWithTask() {
        $task = $this->getMock(\Async\Task\Task::class);
        
        // It should be the exact same task
        $this->assertSame($task, \Async\Util::async($task));
    }
    
    /**
     * Test that Util::async returns a PromiseTask when given a promise
     */
    public function testWithPromise() {
        $promise = new \React\Promise\Deferred();
        
        $task = \Async\Util::async($promise->promise());
        
        // Check it returned an instance of the correct class
        $this->assertInstanceOf(\Async\Task\PromiseTask::class, $task);
        
        // Verify it behaves as if linked to the given promise
        // The behaviour of PromiseTask is verified in more detail in its own
        // test
        $this->assertFalse($task->isComplete());
        
        $promise->resolve('resolved');
        
        $this->assertTrue($task->isComplete());
        $this->assertFalse($task->isFaulted());
        $this->assertEquals('resolved', $task->getResult());
    }
    
    /**
     * Test that Util::async returns a GeneratorTask when given a generator
     */
    public function testWithGenerator() {
        $generator = function() {
            for( $i = 0; $i < 3; $i++ )
                yield new \Async\Task\PromiseTask(\React\Promise\When::resolve($i));
        };
        
        $task = \Async\Util::async($generator());
        
        // Check it returned an instance of the correct class
        $this->assertInstanceOf(\Async\Task\GeneratorTask::class, $task);
        
        // Verify it behaves as if linked to the given promise
        // The behaviour of PromiseTask is verified in more detail in its own
        // test
        $this->assertFalse($task->isComplete());
    }
    
    /**
     * Test that Util::async returns a CallableTask when given a callable
     */
    public function testWithCallable() {
        // The callable expects to be called once
        $callable = $this->getMock(\Async\Test\CallableStub::class);
        $callable->expects($this->once())->method('__invoke');
        
        $task = \Async\Util::async($callable);
        
        // Check it returned an instance of the correct class
        $this->assertInstanceOf(\Async\Task\CallableTask::class, $task);
        
        // Tick the task and verify it has completed and the callable was called
        $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
        $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
        
        $this->assertTrue($task->isComplete());
    }
    
    /**
     * Test that Util::async returns an appropriate CallableTask when given any
     * other object
     * 
     * I.e. one that, when ticked, the task result is the given object
     */
    public function testWithOther() {
        $task = \Async\Util::async(101);
        
        // Check it returned an instance of the correct class
        $this->assertInstanceOf(\Async\Task\CallableTask::class, $task);
        
        // Check the task is currently incomplete
        $this->assertFalse($task->isComplete());
        
        // Tick the task
        $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
        
        // Verify the task is complete and has the correct result
        $this->assertTrue($task->isComplete());
        $this->assertFalse($task->isFaulted());
        $this->assertEquals(101, $task->getResult());
    }
}