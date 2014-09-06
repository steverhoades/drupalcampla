<?php
/**
 * Example to demonstrate how to read a file in a non-blocking way.
 * 
 * This particular script will read 4096 bytes on each iteration of
 * the loop, until there is nothing left to process in which case it
 * will exit.
 *
 * This is only showing an example of reading a single file, this is
 * capable of handling multiple file reads at a time - you would likely
 * have a better way of exiting the loop in that case.
 *
 * the usleep is done to give the CPU time to do other things.
 */
echo PHP_INT_MAX;
exit;
$fp = fopen("alargefile", "r");
stream_set_blocking($fp, 0);
$files = array();
$files[(int) $fp] = $fp;
$buffer = '';
while(true) { //run loop
	if(empty($files)) {
		break;
	}

	$read  = $files;
	$write = $except = NULL;
	$ready = stream_select($read, $write, $except, 0);
	foreach($read as $readFile) {
		$data = fread($readFile, 4096);
		if($data === false || $data == '') {
			unset($files[(int) $readFile]);
			fclose($readFile);
		}

		$buffer .= $data;
	}

	usleep(100);
}

echo sprintf("%2.2f", strlen($buffer)/1000000) . "MB of data was read.". PHP_EOL;