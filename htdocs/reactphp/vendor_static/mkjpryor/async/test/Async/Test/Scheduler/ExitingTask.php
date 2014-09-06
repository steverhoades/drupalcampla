<?php

namespace Async\Test\Scheduler;


class ExitingTask extends \Async\Task\AbstractTask {
    protected function doTick(\Async\Scheduler\Scheduler $scheduler) {
        // Just stop the scheduler
        $scheduler->stop();
    }
}
