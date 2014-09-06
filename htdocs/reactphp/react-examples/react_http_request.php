<?php
require __DIR__ . '/../vendor/autoload.php';

//$loop = React\EventLoop\Factory::create();
$loop = new React\EventLoop\ExtEvLoop();

$dnsFactory = new React\Dns\Resolver\Factory();
$dns = $dnsFactory->create('8.8.8.8', $loop);

$urls = [
	'www.reddit.com',
	'www.hackernews.com',
	'www.google.com',
	'www.yahoo.com',
	'www.amazon.com',
];

$start 		= microtime(true);
$meta 	= [];

/* This is blocking */
foreach($urls as $url) {
	
	$connector = new React\SocketClient\Connector($loop, $dns);
	if(!isset($meta[$url])) {
		$meta[$url] = [
			'responseTime'	=> microtime(true),
			'buffer'		=> '',
			'url' 			=> $url		
		];
	}
	$connector->create($url, 80)->then(
		function (React\Stream\Stream $stream) use (&$meta, $url) {
		    $stream->write("GET / HTTP/1.0\r\nHost: $url\r\nAccept: */*\r\n\r\n");

		    $stream->on('data', function($data, $stream) use (&$meta, $url) {
			    	$meta[$url]['buffer'] .= $data;
			    }
		    );

		    $stream->on('end', function($stream) use (&$meta, $url) { 
		    	$meta[$url]['responseTime'] = (microtime(true) - $meta[$url]['responseTime']);
		    	$stream->close();
		    });
		},
	    function($reason) {
	    	echo "failed ". $reason;
	    }
    );
}
$loop->run();

$total = microtime(true) - $start ;

/*
	Report request response times
 */
$slowest = 0;
foreach($meta as $content) {
	if($content['responseTime'] > $slowest) {
		$slowest = $content['responseTime'];
	}	
	echo $content['url'] .": " . str_pad(
		sprintf("%2.5f", $content['responseTime']), 
		36 - strlen($content['url']), 
		" ", 
		STR_PAD_LEFT
	) . PHP_EOL;
}

echo PHP_EOL;
echo "Total time:          " . str_pad(sprintf("%2.5f", $total), 17 , " ", STR_PAD_LEFT) . PHP_EOL;
echo "Overhead:            " . str_pad(sprintf("%2.5f", $total - $slowest), 17 , " ", STR_PAD_LEFT) . PHP_EOL;
