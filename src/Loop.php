<?php
namespace hedronium\Torus;

use SplObjectStorage;

class Loop
{
    protected $emitters = [];
    protected $handlers = [];

    protected $handle = [];

    protected $timeouts = [];
    protected $intervals = [];

    protected function events(&$events)
    {
        if (is_string($events)) {
            $events = [$events];
        } elseif (is_array($events)) {
            foreach ($events as $event) {
                if (!is_string($event)) {
                    throw new \Exception('Event names must be of string type.');
                }
            }
        } else {
            throw new \Exception('$event must be a string or array of strings.');
        }

        return $events;
    }

    public function addEmitter($emits, callable $emitter, $rate)
    {
        $events = $this->events($emits);

        $obj = new Emitter($events, $emitter, $rate);
        array_push($this->emitters, $obj);

        return $obj;
    }

    public function removeEmitter(Emitter $emitter)
    {
        foreach ($this->emitters as $i=>$obj) {
            if ($emitter === $obj) {
                array_splice($this->emitters, $i, 1);
            }
        }
    }

    public function on($events, callable $handler)
    {
        $events = $this->events($events);

        foreach ($events as $event) {
            if (!isset($this->handle[$event])) {
                $this->handle[$event] = true;
            }
        }

        $obj = new Handler($events, $handler);
        array_push($this->handlers, $obj);

        return $obj;
    }

    public function remove(Handler $handler)
    {
        foreach ($this->handlers as $i=>$obj) {
            if ($handler === $obj) {
                array_splice($this->handlers, $i, 1);
            }
        }
    }

    public function setTimeout($ms, $handler)
    {
        $timeout = new Timeout($ms, $handler);
        array_push($this->timeouts, $timeout);

        return $timeout;
    }

    public function setInterval($ms, $handler)
    {
        $interval = new Interval($ms, $handler);
        array_push($this->intervals, $interval);

        return $interval;
    }

    public function removeTimeout(Timeout $timeout)
    {
        foreach ($this->timeouts as $i=>$obj) {
            if ($timeout === $obj) {
                array_splice($this->timeouts, $i, 1);
            }
        }
    }

    public function removeInterval(Interval $interval)
    {
        foreach ($this->intervals as $obj) {
            if ($interval === $obj) {
                array_splice($this->intervals, $i, 1);
            }           
        }
    }

    public function run($rate = 0)
    {
        $rate *= 1000;

        $time = microtime(true);

        foreach ($this->timeouts as $timeout) {
            $timeout->init($time);
        }

        while (true) {
            $time = microtime(true);
            $queue = [];

            foreach ($this->emitters as $emitter) {
                $events = $emitter->poll($time);

                foreach ($events as $event) {
                    $queue[] = $event;
                }
            }

            foreach ($queue as $event) {
                foreach ($this->handlers as $handler) {
                    $handler->handle($event);
                }
            }

            foreach ($this->timeouts as $i => $timeout) {
                if ($timeout->run($time)) {
                    array_splice($this->timeouts, $i, 1);
                }
            }

            foreach ($this->intervals as $i => $interval) {
                $interval->run($time);
            }

            usleep($rate);
        }
    }
}