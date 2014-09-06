<?php
namespace ExFile;
use React\Partial;
use React\Promise;
use React\Stream\Stream;
use React\Stream\BufferedSink;

class File extends Stream
{ 
	protected $deferred;
	protected $buffer;
	protected $promise;

	public function __construct($file, $mode, $loop)
	{
		$fd = fopen($file, $mode);
		stream_set_blocking($fd, 0);

		parent::__construct($fd, $loop);

		$this->buffer = new BufferedSink();
		$this->pipe($this->buffer);
		$this->on('error', function($reason) { $this->buffer->handleErrorEvent($reason); });
	}

	public function read()
	{
		return $this->buffer->promise();
	}
}
