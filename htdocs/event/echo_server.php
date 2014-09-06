<?php

$handleConnection = function($fd, $event, $arg) { 
    $conn = stream_socket_accept($fd);
    $ip 	 = stream_socket_get_name($conn, true);
    $msg  = stream_socket_recvfrom($conn, 1024);
 
    echo "Incoming connection from: $ip" . PHP_EOL;
    stream_socket_sendto($conn, $msg);
    stream_socket_shutdown($conn, STREAM_SHUT_RDWR);
    fclose($conn);        
};

$sock 	= stream_socket_server('tcp://0.0.0.0:9050', $errno, $errstr);
stream_set_blocking($sock, false);

$base  = new EventBase();
$event = new Event($base, $sock, \EVENT::READ | \EVENT::PERSIST, $handleConnection);
$event->add();
$base->loop();
