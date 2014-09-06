<?php
/**
 * Usage Instructions:
 *
 * If you are going to use port 80 for the HTTP Server than you will need to start this 
 * script as root.  
 *
 * Change to the reveal.js working directory.  Add a directory called slides, this is where you
 * will put the individual slide.html files.  The naming convention for those files is the slide
 * number and then the .html suffix.  In addition to this format you may also use a format like:
 * 1_Introduction.html
 *
 * As you make edits, add or remove files this script will detect those changes and automatically
 * update the sliides web page to reflect those changes.
 *
 * To Run:
 * php /<path>/<to>/<this>/client_refresh_server.php
 *
 * @dependencies inotify pecl extension
 *
 * This script is meant as an example only and should not be used for anything other than experimenting
 * with ReactPHP.  It uses the less effecient StreamSelectLoop polling event loop due to the fact that 
 * LibEvent cannot handle file read streams.
 */
require_once (__DIR__ .'/../../vendor/autoload.php');

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use ExChat\Server as ChatServer;
use ExHttp\Server as ConnectionHandler;
use SlideBuilder\Server as SlideServer;
use SlideBuilder\Builder as SlideBuilder;
use ExHttp\Router;
use ExHttp\StaticFile;
use EventConfig as EventBaseConfig;
use ExHttp\SseMessage as EventMessage;

$cfg = new \EventConfig();
$cfg->requireFeatures(EventBaseConfig::FEATURE_FDS);
$loop 			= new React\EventLoop\ExtEventLoop($cfg);
$documentRoot 	= getcwd();

/* 
	Configure router, the router allows for callbacks to be executed on a route match.  If no
	match is found then it will look to see if it can load a static file.
 */
$connections 		= [];
$router 			= new ExHttp\Router();
$connectionHandler 	= new ConnectionHandler($router);

$fileHandler 		= new StaticFile($documentRoot, $loop);
$router->setFileHandler($fileHandler);

$connectionHandler->on('close', function($conn) use (&$connections) {
	foreach ($connections as $key => $data) {
		$connection = $data['conn'];
		if($connection === $conn) {
			unset($connections[$key]);
			break;
		}
	}
});

$loop->addPeriodicTimer(1, function($timer) use (&$connections) {
	foreach ($connections as $data) {
		echo get_class($data['conn']) . PHP_EOL;
		$connection = $data['conn'];
		$data['msg']->setMessage(array('id' => $data['startedAt'], 'msg' => time()));	
		$connection->send($data['msg']);
	}
});

$router->addRoute('/sse.php', function($request, $response, $connection) use (&$connections) {
		$response->setHeaders([
			'Content-Type' => 'text/event-stream',
			'Cache-Control' => 'no-cache'
		]);
		$startedAt = time();
		
		$msg = new EventMessage($startedAt);
		$msg->setMessage(array('id' => $startedAt, 'msg' => time()));

		$response->setBody($msg);
		$connection->send($response);

		$connections[] = [
			'conn' => $connection, 
			'msg'  => $msg,
			'id'   => $startedAt
		];
	}
);

$httpSocket = new React\Socket\Server($loop);
$httpSocket->listen('80', '0.0.0.0');
$httpServer = new IoServer(
	new HttpServer(
		$connectionHandler		
	),
	$httpSocket, 
	$loop
);

$loop->run();
