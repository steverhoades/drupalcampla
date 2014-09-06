<?php
/**
 * This example will show the React\Promise\race() at work.
 *
 * It will make 2 network calls, the first one to return will
 * be declared the winner and the url will be echo'd to 
 * the terminal.
 * 
 */
require __DIR__ . '/../../vendor/autoload.php';

use React\Promise;
use ExFile\File;

$loop = React\EventLoop\Factory::create();

$loop = React\EventLoop\Factory::create();

$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dnsResolver = $dnsResolverFactory->createCached('8.8.8.8', $loop);

$factory = new React\HttpClient\Factory();
$client = $factory->create($loop, $dnsResolver);

$urls = array(
	'https://github.com/reactphp',
	'https://github.com/cboden',
	'https://github.com/igorw',
	'https://github.com/steverhoades'	
);

$promises = [];
foreach($urls as $url) {
	/* RACER 1 */
	$deferred = new Promise\Deferred();
	$request = $client->request('GET', $url);
	$request->on('response', function ($response) use ($deferred, $url) {
	    $response->on('end', function ($data) use ($deferred, $url) {
	    	 $deferred->resolve($url);
	    });            
	});
	$request->end();
	$promises[] = $deferred->promise();
}

Promise\some($promises, 2)->then(function($values) {
	foreach($values as $value) {
		echo $value . " was one of the first 2 to complete!" . PHP_EOL;
	}
});


$loop->run();