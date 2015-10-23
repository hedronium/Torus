<?php
namespace hedronium\Torus;

use SplObjectStorage;

class Loop
{
	protected $emitters = [];
	protected $handlers = [];

	protected $handle = [];

	protected $timers = [];

	protected function events(&$events)
	{
		if (is_string($events)) {
			$events = [$events];
		} elseif (is_array($events)) {
			foreach ($events as $event) {
				if (!is_string($event)) {
					throw new \Exception('Event names must be of string type.');
				}
			}
		} else {
			throw new \Exception('$event must be a string or array of strings.');
		}

		return $events;
	}

	public function addEmitter($emits, callable $emitter, $rate)
	{
		$events = $this->events($emits);
		array_push($this->emitters, new Emitter($events, $emitter, $rate));
	}

	public function on($events, callable $handler)
	{
		$events = $this->events($events);

		foreach ($events as $event) {
			if (!isset($this->handle[$event])) {
				$this->handle[$event] = true;
			}
		}

		array_push($this->handlers, new Handler($events, $handler));
	}

	public function run($rate = 0)
	{
		$rate *= 1000;

		while (true) {
			$time = microtime(true);
			$queue = [];

			foreach ($this->emitters as $emitter) {
				$events = $emitter->poll($time);

				foreach ($events as $event) {
					$queue[] = $event;
				}
			}

			foreach ($queue as $event) {
				foreach ($this->handlers as $handler) {
					$handler->handle($event);
				}
			}

			usleep($rate);
		}
	}
}