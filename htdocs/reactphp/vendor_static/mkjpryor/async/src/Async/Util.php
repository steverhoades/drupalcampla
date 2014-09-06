<?php

namespace Async;


class Util {
    /**
     * Takes an object and returns an appropriate task object:
     * 
     *   - If $object is a task, it is returned
     *   - If $object is a promise, a PromiseTask is returned
     *   - If $object is a generator, a GeneratorTask is returned
     *   - If $object is callable, a CallableTask is returned that will call the
     *     object only once
     *   - If $object is anything else, a task is returned whose result will be
     *     $object
     * 
     * This method is provided as a convenience for the most commonly used tasks
     * Other tasks can be created directly using their constructors
     * 
     * @param mixed $object
     * @return \Async\Task\Task
     */
    public static function async($object) {
        if( $object instanceof Task\Task )
            return $object;
        
        if( $object instanceof \React\Promise\PromiseInterface )
            return new Task\PromiseTask($object);
        
        if( $object instanceof \Generator )
            return new Task\GeneratorTask($object);
        
        if( is_callable($object) )
            return new Task\CallableTask($object);
        
        return new Task\CallableTask(function() use($object) { return $object; });
    }
    
    /**
     * Shorthand for creating a SomeTask
     * 
     * @param \Async\Task\Task[] $tasks
     * @param integer $howMany
     * @return \Async\Task\SomeTask
     * 
     * @codeCoverageIgnore
     */
    public static function some(array $tasks, $howMany) {
        return new Task\SomeTask($tasks, $howMany);
    }
    
    /**
     * Shorthand for creating an AllTask
     * 
     * @param \Async\Task\Task[] $tasks
     * @return \Async\Task\AllTask
     * 
     * @codeCoverageIgnore
     */
    public static function all(array $tasks) {
        return new Task\AllTask($tasks);
    }
    
    /**
     * Shorthand for creating an AnyTask
     * 
     * @param \Async\Task\Task[] $tasks
     * @return \Async\Task\AnyTask
     * 
     * @codeCoverageIgnore
     */
    public static function any(array $tasks) {
        return new Task\AnyTask($tasks);
    }
    
    /**
     * Util::async is called on the given object to get a task. A new task is
     * returned that delays the start of that task.
     * 
     * @param mixed $object
     * @param float $delay
     * @return \Async\Task\DelayedTask
     * 
     * @codeCoverageIgnore
     */
    public static function delay($object, $delay) {
        return new Task\DelayedTask(static::async($object), $delay);
    }
    
    /**
     * Util::async is called on the given object to get a task. A new task is
     * returned that throttles calls to the tick method of that task.
     * 
     * @param mixed $object
     * @param float $tickInterval
     * @return \Async\Task\ThrottledTask
     * 
     * @codeCoverageIgnore
     */
    public static function throttle($object, $tickInterval) {
        return new Task\ThrottledTask(static::async($object), $tickInterval);
    }
}