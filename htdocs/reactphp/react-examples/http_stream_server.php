<?php
/**
 * An HTTP server written using php streams.
 *
 * This server is not very interesting, but it does demonstrate
 * how to use the Stream classes with React.  You create your
 * stream and then pass it to the EventLoop.
 */
require_once (__DIR__  . '/../vendor/autoload.php');

$loop       = React\EventLoop\Factory::create();
$server     = stream_socket_server('tcp://127.0.0.1:9050', $errno, $errstr);
if(!$server) {
    die("Error: $errno Message: $errstr");
}

$loop->addReadStream($server, function($server) use ($loop) {
    $conn = stream_socket_accept($server);
    $message = "Oh Haiz!  I worked!";

    $res = "HTTP/1.1 200 OK\r\n";
    $res .= "Content-Length: ". strlen($message)  ."\r\n\r\n";
    $res = $message;
    $loop->addWriteStream($conn, function($conn) use (&$res, $loop){
        $bytes = fwrite($conn, $res);

        if($bytes === strlen($res)) {
            fclose($conn);
            $loop->removeStream($conn);
            return;
        }
        // set the response to the new position in the string
        $res = substr($res, 0, $bytes);
    });
});

$loop->run();
