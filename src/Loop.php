<?php
namespace hedronium\Torus;

use SplObjectStorage;

class Loop
{
    protected $pollables = [];
    protected $queue = [];

    public function commit(Eventful $obj)
    {
        $this->pollables[$obj->getObjHash()] = new Pollable($obj);
    }

    public function register(Eventful $obj)
    {
        $obj->register($this);
    }

    protected function poll()
    {
        foreach ($this->pollables as $pollable) {
            $pollable->poll();
        }
    }

    protected function handle()
    {
        foreach ($this->queue as $event) {
            $pollable->handle($event);
        }
    }

    public function pushEvent(Eventful $obj, $event, $data)
    {
        $this->queue[] = new Event($obj, $event, $data);
    }

    public function listen($event, Eventful $obj, callable $callback)
    {
        $this->pollables[$obj->getObjHash()]->addHandler($event, $callback);
    }

    public function stopListening($event, Eventful $obj, callable $callback)
    {
        $this->pollables[$obj->getObjHash()]->removeHandler($event, $callback);
    }

    public function run($rate)
    {
        $last_tick = 0;
        $interval = $rate/1000;
        $sleep_rate = ($rate*1000)/2;

        while (true) {
            $time = microtime(true);

            if (($last_tick+$interval)>$time) {
                usleep($sleep_rate);
            }

            $this->poll();
            $this->handle();

            $last_tick = $time;
        }
    }
}