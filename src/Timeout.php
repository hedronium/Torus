<?php
namespace hedronium\Torus;

class Timeout
{
	protected $timeout = 0;
	protected $start = 0;
	protected $func = null;

	public function __construct($timeout, callable $handler)
	{
		$this->timeout = $timeout/1000;
		$this->func = $handler;
	}

	public function init($time)
	{
		$this->start = $time;
	}

	public function run($time)
	{
		if (($this->start + $this->timeout) > $time) {
			return false;
		}

		$x = $this->func;
		$x();

		return true;
	}
}