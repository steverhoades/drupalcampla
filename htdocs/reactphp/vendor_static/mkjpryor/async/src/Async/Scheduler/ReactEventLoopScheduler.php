<?php

namespace Async\Scheduler;

use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\Timer;

use Async\Task\Task;


class ReactEventLoopScheduler implements Scheduler {
    /**
     * The React event loop we are using to schedule tasks
     *
     * @var \React\EventLoop\LoopInterface
     */
    protected $loop = null;
    
    public function __construct(LoopInterface $loop) {
        $this->loop = $loop;
    }

    /**
     * {@inheritdoc}
     */
    public function schedule(Task $task, $delay = null, $tickInterval = null) {
        // Initial delay and tick interval must be at least Timers::MIN_RESOLUTION
        $delay = max(Timer::MIN_INTERVAL, $delay ?: 0);
        $tickInterval = max(Timer::MIN_INTERVAL, $tickInterval ?: 0);
        
        // Recursive function that ticks the given task and then reschedules itself
        // if the task is not complete
        $tickTask = null;
        $tickTask = function() use($task, $tickInterval, &$tickTask) {
            $task->tick($this);
            if( !$task->isComplete() ) {
                $this->loop->addTimer($tickInterval, $tickTask);
            }
        };
        
        // Add a timer to start the task after the given delay
        $this->loop->addTimer($delay, $tickTask);
    }
    
    /**
     * {@inheritdoc}
     */
    public function run() {
        $this->loop->run();
    }

    /**
     * {@inheritdoc}
     */
    public function stop() {
        $this->loop->stop();
    }
}
