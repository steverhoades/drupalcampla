<?php
// $urls = [
// 	'176.32.98.166'   => 'www.amazon.com',
// 	'198.41.208.142'  => 'www.reddit.com',
// 	'157.166.226.26'  => 'www.cnn.com',
// 	'217.70.184.38'   => 'www.hackernews.com',
// 	'74.125.227.98'   => 'www.google.com',
// 	'98.138.253.109'  => 'www.yahoo.com',
// 	'173.236.168.215' => 'www.stephenrhoades.com'
// 	];
$urls = [
	'www.reddit.com',
	'www.hackernews.com',
	'www.google.com',
	'www.yahoo.com',
	'www.amazon.com',
];

$fds 		= array();
$req 		= [];
$start 		= microtime(true);
$meta 		= [];
/* This is blocking */
foreach($urls as $url) {
	$startStream = microtime(true);	
	$fp = stream_socket_client("tcp://$url:80", $errno, $errstr, 0,  STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT);
	if(!$fp) {
		echo "cannot connect to $url". PHP_EOL;
		continue;
	}

	stream_set_blocking($fp, 0);
	
	$key = (int) $fp;
	$req[$key]= $fp;
	$fds[$key] = $fp;
	$meta[$key] = [
		'responseTime'	=> microtime(true),
		'requestTime'	=> (microtime(true) - $startStream),
		'buffer'		=> '',
		'url' 			=> $url
	];
}

/* This is non-blocking */
while(true) { //run loop
	if(empty($fds)) {
		break;
	}

	$read  = $fds;
	$write = (empty($req)) ? NULL : $req;
	$except = NULL;
	$ready = stream_select($read, $write, $except, 0);
	foreach($read as $readUrl) {
		$key = (int) $readUrl;
		$data = fread($readUrl, 8192);
		
		if($data === false || $data == '') {
			unset($fds[$key]);
			$meta[$key]['responseTime'] = (microtime(true) - $meta[$key]['responseTime']);
			fclose($readUrl);
		}

		//$meta[$key]['buffer'] .= $data;
	}

	if(is_array($write)) {
		foreach($write as $writeUrl) {
			$key = (int) $writeUrl;
			fwrite($writeUrl, "GET / HTTP/1.0\r\nHost: {$meta[$key]['url']}\r\nAccept: */*\r\n\r\n");			
			unset($req[$key]);
		}
	}
}

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
