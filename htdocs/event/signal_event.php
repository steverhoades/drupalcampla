<?php
/**
 * Example is from php.net
 * @see http://us1.php.net/manual/en/event.constructing.signal.events.php
 * 
 * Launch it in a terminal window:
 * 
 * $ php examples/signal.php
 * 
 * In another terminal window find out the pid and send SIGTERM, e.g.:
 * 
 * $ ps aux | grep examp
 * ruslan    3976  0.2  0.0 139896 11256 pts/1    S+   10:25   0:00 php examples/signal.php
 * ruslan    3978  0.0  0.0   9572   864 pts/2    S+   10:26   0:00 grep --color=auto examp
 * $ kill -TERM 3976
 * 
 * At the first terminal window you should catch the following:
 * 
 * Caught signal 15
 */
class MyEventSignal {
    private $base, $ev;

    public function __construct($base) {
        $this->base = $base;
        $this->ev = Event::signal($base, SIGTERM, array($this, 'eventSighandler'));
        $this->ev->add();
    }

    public function eventSighandler($no, $c) {
        echo "Caught signal $no\n";
        $this->base->exit();
    }
}

$base = new EventBase();
$c    = new MyEventSignal($base);

$base->loop();