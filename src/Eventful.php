<?php
namespace Hedronium\Torus;

use Closure;

abstract class Eventful
{
	protected $loop = null;
	protected $pollable_obj = null;

	public function __construct()
	{
		$instance = EventLoop::instance();

		if ($instance->shouldAutoRegister()) {
			$this->register($instance);
		}
	}

	public function __destruct()
	{
		if ($this->loop) {
			$this->loop->remove($this);
		}
	}

	public function register(EventLoop $loop)
	{
		$this->loop = $loop;
		$loop->commit($this);
	}

	public function getPollableObject()
	{
		return $this->pollable_obj;
	}

	public function setPollableObject(Pollable $pollable = null)
	{
		$this->pollable_obj = $pollable;
	}

	protected function isLoopRegistered()
	{
		if ($this->loop === null) {
			throw new \Exception('Not Registered to event loop.');
		}
	}

	protected function emit($event, $data = null, $priority = 0)
	{
		$this->loop->pushEvent($this->pollable_obj, $event, $data, $priority);
	}

	public function trigger($event, $data = null)
	{
		$this->emit($event, $data);
	}

	public function on($event, Closure $callback, $bind = false)
	{
		$this->isLoopRegistered();

		if ($bind) {
			$callback = $callback->bindTo($this);
		}

		$this->pollable_obj->addHandler($event, $callback);
	}

	public function off($event, Closure $callback)
	{
		$this->pollable_obj->removeHandler($event, $callback);
	}

	public function boot(){}

	abstract public function poll();
}