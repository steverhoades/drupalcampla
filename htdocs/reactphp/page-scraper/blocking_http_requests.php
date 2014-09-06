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

$start = microtime(true);
$meta = [];
foreach($urls as $ip => $url) {
	$startStream = microtime(true);
	$startTime = microtime(true);
	$content = file_get_contents("http://$url", false);
	$endTime = (microtime(true) - $startTime);
	$meta[$url] = $endTime;
}
$endTime = (microtime(true) - $start);

/*
	REPORT
 */
echo PHP_EOL;
foreach($meta as $url => $time) {
	echo $url .": " . str_pad(sprintf("%2.5f", $time), 36 - strlen($url), " ", STR_PAD_LEFT) . PHP_EOL;
}
echo PHP_EOL;
echo "Total time:         ". str_pad(sprintf("%2.5f", $endTime), 18, " ", STR_PAD_LEFT) . PHP_EOL;

