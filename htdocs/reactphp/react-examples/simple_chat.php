<?php
require __DIR__ .'/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$server = new React\Socket\Server($loop);
$server->listen('80', '0.0.0.0');

$connections = [];
$server->on('connection', function($conn) use (&$connections) {
	echo $conn->getRemoteAddress() ." connected" . PHP_EOL;
		foreach($connections as $connection) {
			$connection->write($conn->getRemoteAddress() ." connected" . PHP_EOL);
		}	
	$connections[] = $conn;
	$conn->on('data', function($data, $conn) use (&$connections){
		foreach($connections as $connection) {
			if($connection !== $conn) {
				$connection->write($conn->getRemoteAddress() ." said: ". trim($data) . PHP_EOL);
			}
		}
	});
	$conn->on('close', function($conn) use (&$connections) {
		foreach($connections as $key => $connection) {
			if($conn === $connection) {
				unset($connections[$key]);
				break;
			}
		}
	});
});

$loop->run();