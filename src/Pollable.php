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

	public function addHandler($event, callable $callback, $bind = false)
	{
		if (!isset($this->handlers[$event])) {
			$this->handlers[$event] = [];
		}

		$hash = spl_object_hash($callback);

		if ($bind) {
			$callback = $callback->bindTo($this->obj);
		}

		$this->handlers[$event][$hash] = $callback;
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
		$type = $event->type();
		if (!isset($this->handlers[$type])) {
			return;
		}

		$data = $event->data();
		foreach ($this->handlers[$type] as $handler) {
			$handler($event);
		}
	}
}