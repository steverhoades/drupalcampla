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
$socket = stream_socket_server('tcp://0.0.0.0:9050', $errno, $errstr);
if(false === $socket) {
	die("Error: $errno Message: $errstr");
}

while($conn = stream_socket_accept($socket, -1)) {
	$data = fread($conn, 1024);
	$message = "Message Received: $data";
	fwrite($conn, $message, strlen($message));
	fclose($conn);
}
fclose($socket);