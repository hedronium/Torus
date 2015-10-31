<?php
namespace Hedronium\Torus;

class Event
{
	protected $obj = '';
	protected $event = '';
	protected $data = null;

	public function __construct(Pollable $obj, $event, $data = null)
	{
		$this->obj = $obj;
		$this->event = $event;
		$this->data = $data;
	}

	public function getObject()
	{
		return $this->obj;
	}

	public function getData()
	{
		return $this->data;
	}

	public function getType()
	{
		return $this->event;
	}
}