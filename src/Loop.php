<?php
namespace hedronium\Torus;

use SplObjectStorage;

class Loop
{
    protected $pollables = [];
    protected $queue = [];
    protected $time = 0;

    public function time()
    {
        return $this->time;
    }

    public function remove(Eventful $obj)
    {
        $hash = $obj->getObjHash();

        if (isset($this->pollables[$hash])) {
            array_splice(
                $this->pollables, 
                array_search(
                    $hash,
                    array_keys($this->pollables)
                ),
                1
            );
        }
    }

    public function commit(Eventful $obj)
    {
        $this->pollables[$obj->getObjHash()] = new Pollable($obj);
        $obj->boot();
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
        while ($event = array_shift($this->queue)) {
            $this->pollables[$event->getObjHash()]->handle($event);
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

    public function setTimeout(callable $callback, $timeout)
    {
        $timeout = new Timeout($callback, $timeout);
        $timeout->register($this);

        return $timeout;
    }

    public function clearTimeout(Timeout $timeout)
    {
        $this->remove($timeout);
    }

    public function setInterval(callable $callback, $interval)
    {
        $interval = new Interval($callback, $interval);
        $interval->register($this);

        return $interval;
    }

    public function clearInterval(Interval $interval)
    {
        $this->remove($interval);
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

            $this->time = $time;

            $this->poll();
            $this->handle();

            $last_tick = $time;
        }
    }
}