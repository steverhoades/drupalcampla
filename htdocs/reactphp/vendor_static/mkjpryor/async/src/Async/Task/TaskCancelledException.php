<?php

namespace Async\Task;


/**
 * Exception thrown when a task is cancelled
 */
class TaskCancelledException extends \Exception {
    /**
     * The cancelled task
     *
     * @var \Async\Task\Task
     */
    protected $task = null;
    
    public function __construct(Task $task) {
        $this->task = $task;
        
        parent::__construct("Task cancelled");
    }
    
    /**
     * Gets the cancelled task
     * 
     * @return \Async\Task\Task
     */
    public function getCancelledTask() {
        return $this->task;
    }
}