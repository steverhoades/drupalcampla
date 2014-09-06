<?php
/**
 * Simple example of using the PHP stream functions instead of the 
 * socket functions to create a server that listens for connections.
 *
 * This server will block indefinitely and wait for a connection, notice the -1 passed as the
 * second argument to stream_socket_accept.  Once a connection is received it will repsond 
 * with the message.
 * 
 */
$callback = function($fd, $event, $arg) { 
    $conn 	= stream_socket_accept($fd);
    $ip 	= stream_socket_get_name($conn, true);
    echo "Incoming connection from: $ip" . PHP_EOL;

    $msg 	= stream_socket_recvfrom($conn, 1024);
    stream_socket_sendto($conn, $msg);
    stream_socket_shutdown($conn, STREAM_SHUT_RDWR);
    fclose($conn);        
};

$sock 	= stream_socket_server('tcp://0.0.0.0:9050', $errno, $errstr);
$base 	= event_base_new();
$event 	= event_new();
stream_set_blocking($sock, false);

event_set($event, $sock, EV_READ | EV_PERSIST, $callback);
event_set($event, $sock, EV_SIGNAL, function($fd, $event, $arg) { var_dump($event); });
event_base_set($event, $base);
event_add($event);
event_base_loop($base);
