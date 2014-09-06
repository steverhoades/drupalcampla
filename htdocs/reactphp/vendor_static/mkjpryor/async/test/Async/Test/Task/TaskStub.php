<?php

namespace Async\Test\Task;


class TaskStub extends \Async\Task\AbstractTask {
    public function setResult($result) {
        $this->result = $result;
        $this->successful = true;
    }

    public function setException(\Exception $exception) {
        $this->exception = $exception;
    }

    protected function doTick(\Async\Scheduler\Scheduler $scheduler) { /* NOOP */ }
}