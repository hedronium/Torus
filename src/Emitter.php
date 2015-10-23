<?php
namespace hedronium\Torus;

class Emitter
{
	protected $last_poll = 0;
	protected $emits = [];
	protected $func = null;
	protected $rate = 0;

	public function __construct($emits, callable $func, $rate = 0)
	{
		$this->emits = (array) $emits;
		$this->rate = $rate/1000; 
		$this->func = $func;
	}

	public function poll($time)
	{
		if (($this->last_poll+$this->rate) > $time) {
			return [];
		}

		$this->last_poll = $time;
		$x = $this->func;
		return $x();
	}
}