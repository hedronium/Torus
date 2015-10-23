<?php
namespace hedronium\Torus;

class Event
{
	protected $type = '';
	protected $data = null;

	public function __construct($type, $data)
	{
		$this->type = $type;
		$this->data = $data;
	}

	public function type()
	{
		return $this->type;
	}

	public function data()
	{
		return $this->$data;
	}
}