<?php
namespace hedronium\Torus;

class Interval
{
	protected $interval = 0;
	protected $last_run = 0;
	protected $func = null;

	public function __construct($interval, callable $handler)
	{
		$this->interval = $interval/1000;
		$this->func = $handler;
	}

	public function init($time)
	{
		$this->start = $time;
	}

	public function run($time)
	{
		if (($this->last_run + $this->interval) > $time) {
			return false;
		}

		$x = $this->func;
		$x();

		$this->last_run = $time;

		return true;
	}
}