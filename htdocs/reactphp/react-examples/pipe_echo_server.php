<?php
/**
 * This shows an example of PIPING streams together using React.
 *
 * To use telnet into port 4000 and enter any text, you should see
 * that text echo'd back to you in all uppercase letters.
 */
require __DIR__ .'/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);

class UpperStream extends React\Stream\ThroughStream 
{
	public function filter($data)
	{
		return strtoupper($data);
	}
}

$filter = new UpperStream();
$socket->on('connection', function($conn) use ($filter) {
	$conn->pipe($filter)->pipe($conn);
});

$socket->listen(4000);
$loop->run();