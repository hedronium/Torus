<?php
namespace hedronium\Torus;

class Interval extends Eventful
{
	protected $callback = null;
	protected $interval = 0;
	protected $last_run = 0;

	public function __construct(callable $callback, $interval)
	{
		$this->callback = $callback;
		$this->interval = ($interval/1000);
		$this->last_run = microtime(true);
	}

	public function poll()
	{
		if (($this->interval+$this->last_run) <= $this->loop->time()) {
			$this->trigger('interval');
		}
	}

	public function boot()
	{
		$this->on('interval', function (Event $event) {
			$event->object()->run();
		});
	}

	public function run()
	{
		$this->last_run = $this->loop->time();
		
		$x = $this->callback;
		$x();
	}
}