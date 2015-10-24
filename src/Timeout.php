<?php
namespace hedronium\Torus;

class Timeout extends Eventful
{
	protected $callback = null;
	protected $timeout = 0;

	public function __construct(callable $callback, $timeout)
	{
		$this->callback = $callback;
		$this->timeout = microtime(true)+($timeout/1000);
	}

	public function poll()
	{
		if ($this->timeout <= $this->loop->time()) {
			$this->trigger('timeout');
		}
	}

	public function boot()
	{
		$loop = $this->loop;

		$this->on('timeout', function (Event $event) {
			$event->object()->run();
		});

		$this->on('done', function (Event $event) use ($loop) {
			$loop->remove($event->object());
		});
	}

	public function run()
	{
		$x = $this->callback;
		$x();

		$this->trigger('done');
	}

	public function cancel()
	{
		$this->loop->remove($this);
	}
}