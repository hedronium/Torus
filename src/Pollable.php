<?php
namespace hedronium\Torus;

class Pollable
{
	protected $obj = null;
	protected $handlers = [];

	public function __construct(Eventful $obj)
	{
		$this->obj = $obj;
	}

	public function poll()
	{
		$this->obj->poll();
	}

	public function addHandler($event, callable $callback)
	{
		if (!isset($this->handlers[$event])) {
			$this->handlers[$event] = [];
		}

		$this->handlers[$event][spl_object_hash($callback)] = $callback;
	}

	public function removeHandler($event, callable $callback)
	{
		$hash = spl_object_hash($callback);

		if (isset($this->handlers[$event][$hash])) {
			array_splice(
				$this->handlers[$event], 
				array_search(
					$hash,
					array_keys($this->handlers[$event])
				),
				1
			);
		}
	}

	public function handle(Event $event)
	{
		$data = $event->data();
		foreach ($this->handlers[$event->type()] as $handler) {
			$handler($event);
		}
	}
}