<?php
namespace Hedronium\Torus;

use Closure;

class Timeout extends Eventful
{
	protected $callback = null;
	protected $timeout = 0;

	public function __construct(Closure $callback, $timeout)
	{
		$this->callback = $callback;
		$this->timeout = microtime(true)+($timeout/1000);
	}

	public function poll()
	{
		if ($this->timeout <= $this->loop->time()) {
			$this->emit('timeout');
		}
	}

	public function boot()
	{
		$loop = $this->loop;

		$this->on('timeout', function (Event $event) {
			$this->run();
		}, true);

		$this->on('done', function (Event $event) {
			$this->loop->remove($this);
		}, true);
	}

	public function run()
	{
		$x = $this->callback;
		$x();
		
		$this->emit('done');
	}

	public function cancel()
	{
		if ($this->loop) {
			$this->loop->remove($this);
		}
	}
}