<?php

namespace Async\Scheduler;

use Async\Task\Task;


class BasicScheduler implements Scheduler {
    /**
     * Indicates whether the scheduler is currently running
     *
     * @var boolean
     */
    protected $running = false;
    
    /**
     * The queue of tasks to execute next time
     *
     * @var \SplObjectStorage
     */
    protected $next = null;
    
    public function __construct() {
        $this->next = new \SplObjectStorage();
    }
    
    /**
     * {@inheritdoc}
     */
    public function schedule(Task $task, $delay = null, $tickInterval = null) {
        // We don't support the use of timers with this scheduler
        if( null !== $delay || null !== $tickInterval ) {
            throw new \RuntimeException("Timers are not supported by this scheduler implementation");
        }
        
        // Just add the task to be run next tick
        $this->next->attach($task);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function tick() {
        // Get the queue of actions for this tick
        // This is in case any of the actions adds an action to be called on
        // the next tick
        $tasks = $this->next;
        
        // Initialise the queue for next tick
        $this->next = new \SplObjectStorage();
        
        foreach( $tasks as $task ) {
            /* @var $task \Async\Task\Task */
            
            // Execute the task
            $task->tick($this);
            // If the task is not complete, reschedule it for the next tick
            if( !$task->isComplete() ) $this->schedule($task);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function run() {
        $this->running = true;
        
        // Tick until there are no more tasks or we are manually stopped
        do {
            $this->tick();
        } while( $this->running && count($this->next) > 0 );
    }
    
    /**
     * {@inheritdoc}
     */
    public function stop() {
        $this->running = false;
    }
}
