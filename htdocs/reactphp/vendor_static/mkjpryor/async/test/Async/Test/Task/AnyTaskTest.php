<?php

namespace Async\Test\Task;


/**
 * NOTE that AnyTask is a subclass of SomeTask - only AnyTask specific functionality
 * is tested here (i.e. the requirement of only a single task to complete and the
 * result being the result of the completed task rather than a one-element array)
 * 
 * More stringent tests of SomeTask can be found in {@see \Async\Test\Task\SomeTaskTest}
 */
class AnyTaskTest extends \PHPUnit_Framework_TestCase {
    /**
     * Tests that an AnyTask completes successfully after one subtask completes
     * successfully, and that its result is the result of the completed task
     */
    public function testCompletesWithCorrectResultWhenOneSubTaskCompletes() {
        $tasks = [new TaskStub(), new TaskStub(), new TaskStub(), new TaskStub()];
        
        $task = new \Async\Task\AnyTask($tasks);
        
        $this->assertFalse($task->isComplete());
        
        // Fail a task and check that the task has not completed
        $tasks[0]->setException(new \Exception("failure"));
        $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
        $this->assertFalse($task->isComplete());
        
        // Complete a task and check that the task completes successfully with
        // the correct result
        $tasks[1]->setResult(10);
        $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
        
        $this->assertTrue($task->isComplete());
        $this->assertFalse($task->isFaulted());
        $this->assertEquals(10, $task->getResult());
    }
    
    /**
     * Tests that an AnyTask fails only when all the subtasks fail
     */
    public function testFailsWhenAllSubTasksFail() {
        $tasks = [new TaskStub(), new TaskStub(), new TaskStub(), new TaskStub()];
        
        $task = new \Async\Task\AnyTask($tasks);
        
        // Fail each subtask in turn, checking that the task is not yet complete
        $i = 0;
        foreach( $tasks as $subTask ) {
            $i++;
            $this->assertFalse($task->isComplete());
            $subTask->setException(new \Exception("failure $i"));
            $task->tick($this->getMock(\Async\Scheduler\Scheduler::class));
        }
        
        // Check that the task is now complete with an error
        $this->assertTrue($task->isComplete());
        $this->assertTrue($task->isFaulted());
        $this->assertInstanceOf(
            \Async\Task\MultipleFailureException::class, $task->getException()
        );
        $this->assertContains('4 tasks failed', $task->getException()->getMessage());
        $this->assertEquals(
            [
                0 => new \Exception("failure 1"),
                1 => new \Exception("failure 2"),
                2 => new \Exception("failure 3"),
                3 => new \Exception("failure 4")
            ],
            $task->getException()->getFailures()
        );
    }
}