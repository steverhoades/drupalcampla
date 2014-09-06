<?php

namespace Async\Test\Task;


class PromiseTaskTest extends \PHPUnit_Framework_TestCase {
    /**
     * Test that a promise task created with a pre-resolved promise is immediately
     * marked as complete
     */
    public function testPreResolved() {
        $promise = \React\Promise\When::resolve("resolved");
        
        $task = new \Async\Task\PromiseTask($promise);
        
        // The task should be complete with no error
        $this->assertTrue($task->isComplete());
        $this->assertFalse($task->isFaulted());
        
        // The result of the task should be the promise result
        $this->assertEquals("resolved", $task->getResult());
    }
    
    /**
     * Test that a promise task created with a pre-rejected promise is immediately
     * marked as complete with an error
     */
    public function testPreRejected() {
        $promise = \React\Promise\When::reject(new \Exception("rejected"));
        
        $task = new \Async\Task\PromiseTask($promise);
        
        // The task should be complete with an error
        $this->assertTrue($task->isComplete());
        $this->assertTrue($task->isFaulted());
        
        // The message of the task's exception should be that given to the promise
        $this->assertEquals("rejected", $task->getException()->getMessage());
    }
    
    /**
     * Test that a promise task created with a delayed promise becomes complete
     * when the promise is resolved
     */
    public function testDelayedResolution() {
        $promise = new \React\Promise\Deferred();
        
        $task = new \Async\Task\PromiseTask($promise->promise());
        
        // The task should not yet be complete
        $this->assertFalse($task->isComplete());
        
        // Resolve the promise
        $promise->resolve("resolved");
        
        // The task should be complete with no error
        $this->assertTrue($task->isComplete());
        $this->assertFalse($task->isFaulted());
        
        // The result of the task should be the promise result
        $this->assertEquals("resolved", $task->getResult());
    }
    
    /**
     * Test that a promise task created with a delayed promise becomes complete
     * when the promise is rejected
     */
    public function testDelayedRejection() {
        $promise = new \React\Promise\Deferred();
        
        $task = new \Async\Task\PromiseTask($promise->promise());
        
        // The task should not yet be complete
        $this->assertFalse($task->isComplete());
        
        // Reject the promise
        $promise->reject(new \Exception("rejected"));
        
        // The task should be complete with an error
        $this->assertTrue($task->isComplete());
        $this->assertTrue($task->isFaulted());
        
        // The message of the task's exception should be that given to the promise
        $this->assertEquals("rejected", $task->getException()->getMessage());
    }
    
    /**
     * Test that tick is a noop for a promise task
     */
    public function testTickNoop() {
        $promise = new \React\Promise\Deferred();
        
        $task = new \Async\Task\PromiseTask($promise->promise());
        
        // The task should not yet be complete
        $this->assertFalse($task->isComplete());
        
        // Tick a few times
        $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
        $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
        $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
        
        // The task should not have completed
        $this->assertFalse($task->isComplete());
        
        // Resolve the promise
        $promise->resolve("resolved");
        
        // The task should be complete with no error
        $this->assertTrue($task->isComplete());
        $this->assertFalse($task->isFaulted());        
        // The result of the task should be the promise result
        $this->assertEquals("resolved", $task->getResult());
        
        // Tick a few more times
        $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
        $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
        $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
        
        // Verify that the result is unchanged
        $this->assertTrue($task->isComplete());
        $this->assertFalse($task->isFaulted());        
        // The result of the task should be the promise result
        $this->assertEquals("resolved", $task->getResult());
    }
}
