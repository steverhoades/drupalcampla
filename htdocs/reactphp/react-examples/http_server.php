<?php
require_once (__DIR__  . '/../vendor/autoload.php');

$loop       = React\EventLoop\Factory::create();
$socket     = new React\Socket\Server($loop);
$httpServer = new React\Http\Server($socket);

$httpServer->on('request', function($req, $res) {
	$res->writeHead(200, array('Content-Type' => 'text/plain'));
	$res->end('Hello World!');
});

$socket->listen('8080');
$loop->run();
