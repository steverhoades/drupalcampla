<?php
require_once(__DIR__ . '/../vendor/autoload.php');

use Ratchet\Server\IoServer;
use ExChat\Server as ChatServer;

$server = IoServer::factory(
	new ChatServer(),
	8080
);
$server->run();