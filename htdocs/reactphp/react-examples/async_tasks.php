<?php
/**
 * This example uses a library heavily inspired by nikic's article on
 * coopertive multi-tasking with Generators and Coroutines.
 *
 * http://bitbucket.org/mkjpryor/async
 *
 * to end ctrl+c.
 *
 * Requires PHP 5.5+
 */
require __DIR__ .'/../vendor/autoload.php';
use Async\Util;


/*
 * Class for a buffer of values where potential future reads are represented with promises
 */
class Buffer {
    protected $reads, $data;

    public function __construct() {
        $this->reads = new SplQueue();
        $this->data = new SplQueue();
    }

    /**
     * Return a promise that will be fulfilled with a value at some point in the future
     *
     * @return \React\Promise\PromiseInterface
     */
    public function read() {
        if( $this->data->isEmpty() ) {
            $deferred = new \React\Promise\Deferred();
            $promise = $deferred->promise();
            $this->reads->enqueue($deferred);
            return $promise;
        } else {
            return \React\Promise\resolve($this->data->dequeue());
        }
    }

    /**
     * Write a string to the buffer that can be used to fulfil a promise
     *
     * @param string $str
     */
    public function write($str) {
        if( $this->reads->isEmpty() ) {
            $this->data->enqueue($str);
        } else {
            $this->reads->dequeue()->resolve($str);
        }
    }
}


/*
 * Generator that prints a value from a buffer and then defers to nested_printer
 */
function printer(Buffer $buffer) {
    while( true ) {
        // Yield a promise task and wait for the result - this is non-blocking
        $value = ( yield Util::async($buffer->read()) );

        echo "Printer: ", $value, PHP_EOL;

        // Yield a generator task for nested_printer and wait for it to complete
        // This is also non-blocking
        // Since a generator task has no result, we don't need to put it into a variable
        yield Util::async(nested_printer($buffer));
    }
}

/*
 * Generator that prints 5 values from a buffer
 */
function nested_printer(Buffer $buffer) {
    for( $i = 0; $i < 5; $i++ ) {
        // Yield a promise task and wait for the result - this is non-blocking
        $value = ( yield Util::async($buffer->read()) );

        echo "Nested printer: ", $value, PHP_EOL;
    }
}


// Create a new buffer
$buffer = new Buffer();

$loop   = new React\EventLoop\ExtEventLoop();

// Create a new scheduler
$scheduler = new \Async\Scheduler\ReactEventLoopScheduler($loop);

// Schedule a generator task for the printer
$scheduler->schedule(new \Async\Task\GeneratorTask(printer($buffer)));

// Schedule a recurring task that writes incrementing integers to the buffer
$i = 0;
$scheduler->schedule(new \Async\Task\RecurringTask(
    function() use($buffer, &$i) { $buffer->write(++$i); }
));

// Run the scheduler
$scheduler->run();