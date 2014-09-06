<?php

namespace Async\Test\Scheduler;


class AddTaskTask extends \Async\Task\AbstractTask {
    protected $childTask = null;
    
    public function __construct(\Async\Task\Task $task) {
        $this->childTask = $task;
    }
    
    protected function doTick(\Async\Scheduler\Scheduler $scheduler) {
        // Just schedule the task that we were told to schedule
        $scheduler->schedule($this->childTask);
        
        // Once we have done our work of scheduling the child task, we are successful
        $this->successful = true;
    }
}
