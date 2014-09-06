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

/* 
	Using Select Loop due to LibEvent epolling which doesn't work with file reads 
	@see http://stackoverflow.com/questions/5456967/problem-handling-file-i-o-with-libevent2
	@todo look at possibly using libev
*/
//$loop 			= new React\EventLoop\StreamSelectLoop();
$cfg = new \EventConfig();
$cfg->requireFeatures(EventBaseConfig::FEATURE_FDS);
$loop 			= new React\EventLoop\ExtEventLoop($cfg);
$documentRoot 	= getcwd();
$slidesDir 		= $documentRoot . DIRECTORY_SEPARATOR . 'slides';

echo "Watching directory: ". $documentRoot . PHP_EOL;

/* 
	slide server and builder 
*/
$slideBuilder = new SlideBuilder($loop, $slidesDir);
$slideServer  = new SlideServer($slideBuilder);

/* 
	Directory monitor 
*/
$inotify = new MKraemer\ReactInotify\Inotify($loop);
$inotify->add($documentRoot . '/slides/', IN_CREATE | IN_DELETE | IN_MODIFY);
$inotify->on(IN_MODIFY, array($slideBuilder, 'buildSlides'));
$inotify->on(IN_CREATE, array($slideBuilder, 'buildSlides'));
$inotify->on(IN_DELETE, array($slideBuilder, 'buildSlides'));

/* 
	once slides have finished building index.html notify any connected clients to refresh 
*/
$slideBuilder->on('slidesUpdated', array($slideServer, 'notify'));

/* 
	initialize slides 
*/
$slideBuilder->buildSlides();

/* 
	add contents of client side HTML that will handle auto-refreshing slides to route 
*/
$clientBootstrap = file_get_contents(__DIR__ . '/index.html');

/* 
	Configure router, the router allows for callbacks to be executed on a route match.  If no
	match is found then it will look to see if it can load a static file.
 */
$router = new ExHttp\Router(array(
	"/" 			=> function($request, $response, $connection) use ($clientBootstrap) {
		$response->setBody($clientBootstrap);
		$connection->send($response);
		$connection->close();
	},

	'/slides.html' 	=> function($request, $response, $connection) use ($slideBuilder) {
		$response->setBody($slideBuilder->getLastBuild());
		$connection->send($response);
		$connection->close();
	}
));

$fileHandler = new StaticFile($documentRoot, $loop);
$router->setFileHandler($fileHandler);

/* 
	Websocket and HTTP Server Sockets 
*/
$socketServer = new React\Socket\Server($loop);
$socketServer->listen('8080', '0.0.0.0');
$websocketServer = new IoServer(
	new HttpServer(
		new WsServer(
			$slideServer
		)
	),
	$socketServer,
	$loop
);

$httpSocket = new React\Socket\Server($loop);
$httpSocket->listen('80', '0.0.0.0');
$httpServer = new IoServer(
	new HttpServer(
		new ConnectionHandler($router)
	),
	$httpSocket, 
	$loop
);

$loop->run();
