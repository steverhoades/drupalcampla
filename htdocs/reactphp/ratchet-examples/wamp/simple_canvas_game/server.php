<?php
/**
 * Very simple example of using WAMP v1 and autobahn JS to write a 'massively' 
 * multiplayer type game. 
 *
 * client game mechanics: js/game.js
 * server: server.php
 *
 * Adapted from:
 * https://github.com/lostdecade/simple_canvas_game
 * 
 * To run, you will need to make sure you have sudo access as it is currently
 * configured to run the web server on port 80 and have port 8080 accessible
 * as that is where the websocket server is configured to listen.
 *
 * when done, point your browser to port 80 of your machine and you should see
 * a little avatar pop up - add more browsers to see more avatars.
 *
 * To chat simply enter a message and hit enter or press the send button.
 */
require_once('/var/www/reactphp/vendor/autoload.php');

use ExHttp\Server as ConnectionHandler;
use ExHttp\StaticFile;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$cfg = new \EventConfig();
$cfg->requireFeatures(\EventConfig::FEATURE_FDS);
$loop 			= new React\EventLoop\ExtEventLoop($cfg);
$documentRoot 	= getcwd();

// Set up our WebSocket server for clients wanting real-time updates
$webSock = new React\Socket\Server($loop);
$webSock->listen(8080, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect
$webServer = new Ratchet\Server\IoServer(
    new Ratchet\Http\HttpServer(
        new Ratchet\WebSocket\WsServer(
            new Ratchet\Wamp\WampServer(
                new ExGame\Server()
            )
        )
    ),
    $webSock
);

$router = new ExHttp\Router();
$fileHandler = new StaticFile($documentRoot, $loop);
$router->setFileHandler($fileHandler);

$httpSocket = new React\Socket\Server($loop);
$httpSocket->listen(80, '0.0.0.0');
$httpServer = new IoServer(
	new HttpServer(
		new ConnectionHandler($router)
	),
	$httpSocket, 
	$loop
);

$loop->run();