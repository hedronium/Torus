<?php
namespace Hedronium\Torus;

use SplObjectStorage;
use Closure;

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

	public function getObject()
	{

	}

	public function addHandler($event, Closure $callback)
	{
		if (!isset($this->handlers[$event])) {
			$this->handlers[$event] = new SplObjectStorage;
		}

		$this->handlers[$event]->attach($callback);
	}

	public function removeHandler($event, Closure $callback)
	{
		$this->handlers[$event]->detach($callback);
	}

	public function handle(Event $event)
	{
		$type = $event->getType();
		if (!isset($this->handlers[$type])) {
			return;
		}

		foreach ($this->handlers[$type] as $handler) {
			$handler($event);
		}
	}
}