<?php
require __DIR__ . '/../vendor/autoload.php';

/** @var $loop \React\EventLoop\ExtEventLoop */
$cfg = new \EventConfig();
$cfg->requireFeatures(\EventConfig::FEATURE_FDS);
$loop = new \React\EventLoop\ExtEventLoop($cfg);

$dir = getcwd();
$fd = fopen($dir . DIRECTORY_SEPARATOR . 'test.dmg', "r");
stream_set_blocking($fd, 0);

$stream = new \React\Stream\Stream($fd, $loop);

$stream->on('data',  function() { echo 'test'. PHP_EOL; });
$loop->addPeriodicTimer(0.01, function() { echo "Timer". PHP_EOL; });

$loop->run();
