<?php
namespace hedronium\Torus;

abstract class Eventful
{
	protected $loop = null;
	protected $objHash = null;

	abstract public function poll();

	public function boot(){}

	public function getObjHash()
	{
		if ($this->objHash) {
			return $this->objHash;
		}

		return $this->objHash = spl_object_hash($this);
	}

	protected function checkEventLoopRegistrationStatus()
	{
		if ($this->loop === null) {
			throw new \Exception('Not Registered to event loop.');
		}
	}

	protected function trigger($event, $data = null)
	{
		$this->loop->pushEvent($this, $event, $data);
	}

	public function register(Loop $loop)
	{
		$this->loop = $loop;
		$loop->commit($this);
	}

	public function on($event, callable $callback, $bind = false)
	{
		$this->checkEventLoopRegistrationStatus();
		
		$this->loop->listen($event, $this, $callback, $bind);
	}

	public function off($event, callable $callback)
	{
		$this->loop->stopListening($this, $callback);
	}
}