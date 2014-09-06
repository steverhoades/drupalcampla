<?php
require __DIR__ .'/../vendor/autoload.php';

// use React\EventLoop\Factory as LoopFactory;
// $loop = React\EventLoop\Factory::create();

// $tempDir  = sys_get_temp_dir();
// $tempFileRead = tempnam($tempDir, "exr");
// $tempFileWrite = tempname($tempDir, "exw");
// $stream = fopen($tempFileRead, "r");
// stream_set_blocking($stream, false);

// $loop->run();

// $stream = new MyCompositeStream($readableStream, $writableStream);


// $stream 	  = fopen($file, "r");	
// stream_set_blocking($stream, 0);

// $readableStream->pipe($writeableStream);

// $stream   = new Stream($fd, $this->loop);
// $stream->on('data',  function($data) { echo "data\n"; });
// $stream->on('close', function($data) { echo "close\n"; });
// $stream->on('error', function($data) { echo "error\n"; });
// //if writeable
// $stream->on('drain', function($data) { echo "drain\n"; });

/**
 * Readable stream example
 */
class ChatClient extends React\Stream\ReadableStream
{
    public function say($message)
    {
        $this->emit('data', [sprintf("GLOBAL:%s\n", $this->escape($message))]);
    }

    private function escape($input)
    {
        return str_replace(["\n", ':'], ['\\n', ''], $input);
    }
}

$client->pipe($conn);
$client->say("Hello everyone!");

/**
 * Writable stream example
 */
class TokenParser extends React\Stream\WritableStream
{
    public function write($data)
    {
    	$data = trim($data);
    	$this->emit('tokenData', explode("|", $data));
    }
}

$chat = new TokenParser();
$chat->on('tokenData', function($name, $bid) {
	echo "Bid: $bid for $name received" . PHP_EOL;
});

$conn->pipe($chat);

/**
 * Through stream example.
 */
class ShoutFilter extends React\Stream\ThroughStream
{
    public function filter($data)
    {
        return strtoupper($data);
    }
}




// socket based chat
$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);

$conns = new \SplObjectStorage();




$shout = new ShoutFilter();

$socket->on('connection', function ($conn) use ($conns, $shout, $chat) {
    $conns->attach($conn);
    $conn->pipe($shout)->pipe($conn)->pipe($chat);
    $conn->on('data', function ($data) use ($conns, $conn) {
        foreach ($conns as $current) {
            if ($conn === $current) {
                continue;
            }

            $current->write($conn->getRemoteAddress().': ');
            $current->write($data);
        }
    });

    $conn->on('end', function () use ($conns, $conn) {
        $conns->detach($conn);
    });
});

echo "Socket server listening on port 4000.\n";
echo "You can connect to it by running: telnet localhost 4000\n";

$socket->listen(4000);
$loop->run();

