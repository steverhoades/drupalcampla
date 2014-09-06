<?php

namespace Async\Task;

use Async\Scheduler\Scheduler;


class GeneratorTask extends AbstractTask {
    /**
     * The generator we are using
     *
     * @var \Generator
     */
    protected $generator = null;
    
    /**
     * The task that the generator is paused waiting for
     *
     * @var \Async\Task\Task
     */
    protected $waiting = null;
    
    /**
     * Creates a new task using the given generator
     * 
     * @param \Generator $generator
     */
    public function __construct(\Generator $generator) {
        $this->generator = $generator;
    }
    
    /**
     * {@inheritdoc}
     */
    public function isSuccessful() {
        // We have successfully completed if the generator has no more items
        return !$this->generator->valid();
    }

    /**
     * {@inheritdoc}
     */
    protected function doTick(Scheduler $scheduler) {
        // Check if we are waiting for a task to complete in order to pass the
        // result to our generator
        if( $this->waiting ) {
            if( $this->waiting->isComplete() ) {
                // If the task we are waiting for is complete, we need to resume
                // the generator, passing the result of running the task
                // We want to catch any errors that are thrown
                try {
                    if( $this->waiting->isFaulted() ) {
                        // If the task we are waiting for has failed, throw it's
                        // exception into the generator
                        $this->generator->throw($this->waiting->getException());
                    }
                    else if( $this->waiting->isCancelled() ) {
                        // If the task we are waiting for was cancelled, throw a
                        // TaskCancelledException into the generator
                        $this->generator->throw(new TaskCancelledException($this->waiting));
                    }
                    else {
                        // Otherwise, send the result back
                        $this->generator->send($this->waiting->getResult());
                    }
                }
                catch( \Exception $e ) {
                    $this->exception = $e;
                }
            }
            else {
                // If the task we are waiting for is not complete, there is nothing
                // to do on this tick
                return;
            }
            
            // If we get this far, we are done waiting for that task
            $this->waiting = null;
        }
        
        // If we are complete, there is nothing more to do
        if( $this->isComplete() ) return;
        
        // Otherwise, we wait on the yielded task to complete
        $this->waiting = $this->generator->current();
        // Schedule the task we are waiting on with the scheduler
        $scheduler->schedule($this->waiting);
    }
}
