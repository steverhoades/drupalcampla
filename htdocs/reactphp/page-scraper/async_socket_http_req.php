<?php
// create a streaming socket, of type TCP/IP
$urls = [
	'198.41.208.142'  => 'www.reddit.com',
	'217.70.184.38'   => 'www.hackernews.com',
	'74.125.227.98'   => 'www.google.com',
	'98.138.253.109'  => 'www.yahoo.com',
	'176.32.98.166'   => 'www.amazon.com',
];
$fds 		= array();
$req 		= array();
$start 		= microtime(true);
$meta 	= [];

/* This is blocking */
foreach($urls as $ip => $url) {
	
	$fp = socket_create(AF_INET, SOCK_STREAM , SOL_TCP);
	$startStream = microtime(true);	
	socket_connect($fp, $ip, 80);
	echo (microtime(true) - $startStream) . PHP_EOL;
	socket_set_nonblock($fp);
	
	$key = (int) $fp;
	$fds[$key] = $fp;
	$req[$key] = $fp;
	$meta[$key] = [
		'responseTime'	=> microtime(true),
		'requestTime'	=> (microtime(true) - $startStream),
		'buffer'		=> '',
		'url' 			=> $url
	];
}

$requestTotal = (microtime(true) - $start);

/* This is non-blocking */
$selectStart = microtime(true);
while(true) { //run loop
	if(empty($fds)) {
		break;
	}

	$read  = $fds;
	$write = (!empty($req)) ? $req : NULL;
	$except = NULL;
	$ready = socket_select($read, $write, $except, 0);
	foreach($read as $readUrl) {
		$key = (int) $readUrl;
		$data = socket_recv($readUrl, $meta[$key]['buffer'], 8192, MSG_DONTWAIT);
		
		if($data === false || $data == '') {
			unset($fds[$key]);			
			$meta[$key]['responseTime'] = (microtime(true) - $meta[$key]['responseTime']);
			socket_close($readUrl);
		}
	}

	if(is_array($write)) {
		foreach($write as $writeUrl) {
			$key = (int) $writeUrl;

			$msg = "GET / HTTP/1.0\r\nHost: {$meta[$key]['url']}\r\nAccept: */*\r\n\r\n";
			socket_send($writeUrl, $msg, strlen($msg), 0);			
			unset($req[$key]);
		}
	}
}
$responseTotal = (microtime(true) - $selectStart);

$total = microtime(true) - $start ;

/*
	Report request response times
 */
foreach($meta as $content) {
	echo $content['url'] .": " . str_pad(
		sprintf("%2.5f", $content['requestTime'] + $content['responseTime']), 
		36 - strlen($content['url']), 
		" ", 
		STR_PAD_LEFT
	) . PHP_EOL;
}

echo PHP_EOL;
echo "Request total time:  " . str_pad(sprintf("%2.5f", $requestTotal), 17 , " ", STR_PAD_LEFT) . PHP_EOL;
echo "Response total time: " . str_pad(sprintf("%2.5f", $responseTotal), 17 , " ", STR_PAD_LEFT) . PHP_EOL;
echo "Total time:          " . str_pad(sprintf("%2.5f", $total), 17 , " ", STR_PAD_LEFT) . PHP_EOL;
