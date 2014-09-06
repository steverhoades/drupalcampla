<?php

namespace SlideBuilder;

use Evenement\EventEmitter;
use React\Stream\Stream;
use React\Promise\Deferred;
use React\Partial;
use React\Promise;
use ExFile\File;

class Builder extends EventEmitter
{
	protected $loop;
	protected $buildDir;
	protected $buffer = [];
	protected $slideCache;
	protected $template;

	public function __construct($loop, $buildDir)
	{
		$this->loop = $loop;		
		$this->buildDir = $buildDir;
		$this->template = file_get_contents(dirname($this->buildDir) . '/index.html');
	}

	public function buildSlides($path = null)
	{
		if(!is_null($path)) {
			echo "File $path changed. ". PHP_EOL;
		}

		$promises 	= array();
		$files 		= scandir($this->buildDir);

		foreach($files as $file) {
			$matches = array();
			/* Format of slide file should be <int>.html OR <int>_<name>.html */
			preg_match('/(\d+)(_[a-zA-Z-_]+)?\.html$/', $file, $matches);			
			if(!empty($matches[1])) {

				//$fd 	  = fopen($this->buildDir . DIRECTORY_SEPARATOR . $file, "r");
				$file 	  = new File($this->buildDir . DIRECTORY_SEPARATOR . $file, "r", $this->loop);
				/* @note LibEvent doesn't handle file reads asynchronously (non-blocking) */				
				//stream_set_blocking($fd, 0);
				//$stream   = new Stream($fd, $this->loop);
				//$deferred = new Promise\Deferred();
				// $stream->on('data',  Partial\bind([$this, 'onData'],  $matches[1]));
				// $stream->on('close', Partial\bind([$this, 'onClose'], $deferred, $matches[1]));
				// $stream->on('error', Partial\bind([$this, 'onError'], $deferred));
				
				$promises[] = $file->read();
			}
		}

		$promise = Promise\all($promises)->then(array($this, 'build'));
	}

	public function getLastBuild()
	{
		return $this->slideCache;
	}

	public function build($fileBuffers)
	{
		var_dump($fileBuffers);
		echo "building". PHP_EOL;
		ksort($fileBuffers);
		$html = str_replace('<div class="slides">', '<div class="slides">'. implode("\n", $fileBuffers), $this->template);
		$this->slideCache = $html;

		$this->emit('slidesUpdated', array($html, $this));
	}
}
