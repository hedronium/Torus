<?php
namespace hedronium\Torus;

class Handler
{
	protected $handles = [];
	protected $func = null;

	public function __construct($handles, callable $func)
	{
		$this->handles = array_flip((array) $handles);
		$this->func = $func;
	}

	public function handle(Event $event)
	{
		if (!isset($this->handles[$event->type()])) {
			return;
		}

		$x = $this->func;
		$x($event);
	}
}