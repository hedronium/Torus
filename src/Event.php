<?php
namespace hedronium\Torus;

class Event
{
	protected $objHash = '';
	protected $obj = '';
	protected $event = '';
	protected $data = null;

	public function __construct(Eventful $obj, $event, $data = null)
	{
		$this->objHash = $obj->getObjHash();
		$this->obj = $obj;
		$this->event = $event;
		$this->data = $data;
	}

	public function object()
	{
		return $this->obj;
	}

	public function getObjHash()
	{
		return $this->objHash;
	}

	public function data()
	{
		return $this->data;
	}

	public function type()
	{
		return $this->event;
	}
}