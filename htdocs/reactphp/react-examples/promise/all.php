<?php
/**
 * This example will show the React\Promise\all() at work.
 */
require __DIR__ . '/../../vendor/autoload.php';

use React\Promise;
use ExFile\File;

$cfg = new \EventConfig();
$cfg->requireFeatures(\EventConfig::FEATURE_FDS);
$loop 			= new React\EventLoop\ExtEventLoop($cfg);

$file1 = new file(__DIR__ . '/_files/test_file.txt', "r", $loop);
$file2 = new file(__DIR__ . '/_files/test_file2.txt', "r", $loop);

$json = function($value) { 
	return (array) json_decode($value); 
};

$promises = array(
	$file1
		->read()
		->then($json), 
	$file2
		->read()
		->then($json)
);

Promise\all($promises)->then(function($values) {
	foreach($values as $value) {
		echo $value['name'] .": ". $value['url'] . PHP_EOL;
	}
});


$loop->run();