<?php
require __DIR__ .'/../vendor/autoload.php';

use React\EventLoop\Factory as LoopFactory;
$loop = React\EventLoop\Factory::create();

$loop->addTimer(1, function(){
	$args = func_get_args();
	echo "I'm a one off timer, so nope!" . PHP_EOL;
});

$loop->addPeriodicTimer(1, function($timer){
	echo "Yes, I am annoying =)" . PHP_EOL;
	// cancel that annoying timer
	$timer->cancel();
});

$loop->run();