<?php
namespace hedronium\Torus;

class Event
{
	protected $obj = '';
	protected $event = '';
	protected $data = null;

	public function __construct(Eventful $obj, $event, $data)
	{
		$this->obj = $obj->getObjHash();
		$this->event = $event;
		$this->data = $data;
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