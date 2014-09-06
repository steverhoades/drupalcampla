<?php
// create base and event
$base = event_base_new();
$event = event_new();

// Allocate a new context
$context = new ZMQContext();

// Create sockets
$rep = $context->getSocket(ZMQ::SOCKET_REP);

// Connect the socket
$rep->bind("tcp://127.0.0.1:5555");

// Get the stream descriptor
$fd = $rep->getsockopt(ZMQ::SOCKOPT_FD);

// set event flags
event_set(
    $event, 
    $fd, 
    EV_READ | EV_PERSIST, 
    function($fd, $events, $arg) { 
        if($arg[0]->getsockopt (ZMQ::SOCKOPT_EVENTS) & ZMQ::POLL_IN) {
            $msg = $arg[0]->recv();
            echo "Got incoming data: $msg" . PHP_EOL;
            $arg[0]->send("Received message: $msg");
        }
    }, 
    array($rep, $base)
);

// set event base
event_base_set($event, $base);

// enable event
event_add($event);

// start event loop
event_base_loop($base);