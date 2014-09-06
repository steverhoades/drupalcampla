<?php

namespace Async\Task;

use React\Promise\PromiseInterface;


/**
 * Task that waits for a React promise to complete
 */
class PromiseTask extends AbstractTask {
    /**
     * Create a new task from the given promise
     * 
     * @param \React\Promise\PromiseInterface $promise
     */
    public function __construct(PromiseInterface $promise) {
        // When the promise completes, we want to store the result to be
        // processed on the next tick
        $promise->then(
            function($result) {
                $this->successful = true;
                $this->result = $result;
            },
            function(\Exception $exception) {
                $this->exception = $exception;
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function doTick(\Async\Scheduler\Scheduler $scheduler) {
        /*
         * This is a NOOP for a promise task - all the work is done in the
         * callbacks given to then
         */
    }
}
