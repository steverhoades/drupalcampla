<?php
/**
 * Example Echo Server using LibEvent
 *
 * To run execute the following on the command line: 
 * php libevent_echo_server.php
 *
 * Note: 
 * 
 * The socket functions are used in this example.  You could also use
 * the stream functions.  This would cut down on a few function calls.
 * 
 * $socket = stream_socket_server("tcp://0.0.0.0:80", $errno, $errstr);
 * $connection = stream_socket_select($fd)
 * 
 */
$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

socket_bind($sock, '0.0.0.0', 1337);
socket_listen($sock, 10);
socket_set_nonblock($sock);

$base   = event_base_new();
$event  = event_new();
$callback = function($fd, $event, $arg) { 
    if(false === ($conn = socket_accept($fd))) {
        echo "Unable to connect to incoming socket\n";
        return;
    }
    echo "Received incoming connection\n";

    $msg = socket_read($conn, 1024);
    $msg = trim($msg);
    if(false === socket_write($conn, $msg, strlen($msg))) {
        echo "Unable to write to socket\n";
    }

    socket_close($conn);        
};

event_set($event, $sock, EV_READ | EV_PERSIST, $callback );

event_base_set($event, $base);
event_add($event);
event_base_loop($base);
