<?php
namespace Hedronium\Torus;

use SplPriorityQueue;
use SplObjectStorage;
use Closure;

class EventLoop
{
    protected static $default_instance = null;

    public static function instance()
    {
        if (self::$default_instance) {
            return self::$default_instance;
        }

        return (self::$default_instance = new self);
    }

    protected static function setInstance(self $instance = null)
    {
        self::$default_instance = $instance;
    }

    protected $auto_register = true;
    protected $pollables = null;
    protected $queue = null;
    protected $time = 0;
    protected $run  = false;

    protected $pollable_factory = null;
    protected $event_factory = null;

    public function __construct($auto_register = true)
    {
        $this->auto_register = $auto_register;
        $this->pollables = new SplObjectStorage;
        $this->queue = new SplPriorityQueue;

        $this->pollable_factory = function () {
            return new Pollable(...func_get_args());
        };

        $this->event_factory = function () {
            return new Event(...func_get_args());
        };

        self::setInstance($this);
    }

    public function setPollableFactory(Closure $pollable_factory)
    {
        $this->pollable_factory = $pollable_factory;
    }

    public function setEventFactory(Closure $interval_factory)
    {
        $this->interval_factory = $interval_factory;
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function shouldAutoRegister()
    {
        return $this->auto_register;
    }

    public function register(Eventful $obj)
    {
        $obj->register($this);
    }

    public function commit(Eventful $obj)
    {
        $facotry = $this->pollable_factory;
        $pollable = $facotry($obj);
        $this->pollables->attach($pollable);
        $obj->setPollableObject($pollable);
        $obj->boot();

        return $pollable;
    }

    public function isRegistered(Eventful $obj)
    {
        return isset($this->pollables[$obj->getPollableObject()]);
    }

    public function remove(Eventful $obj)
    {
        $this->pollables->detach($obj->getPollableObject());
    }

    public function pushEvent(Pollable $obj, $event, $data, $priority = 0)
    {
        $facotry = $this->event_factory;
        $event = $facotry($obj, (string) $event, $data);
        $this->queue->insert($event, (int) $priority);

        return $event;
    }

    protected function poll()
    {
        foreach ($this->pollables as $pollable) {
            $pollable->poll();
        }
    }

    protected function handle()
    {
        while (!$this->queue->isEmpty()) {
            $event = $this->queue->extract();
            $obj = $event->getObject()->handle($event);
        }
    }

    public function time()
    {
        return $this->time ? $this->time : $this->time = microtime(true);
    }

    public function run($rate = 20, $singular = false)
    {
        $last_tick = 0;
        $interval = $rate/1000;
        $sleep_rate = ($rate*1000)/2;
        $this->run = true;

        while ($this->run) {
            $time = microtime(true);

            if (($last_tick+$interval)>$time) {
                usleep($sleep_rate);
            }

            $this->time = $time;

            $this->poll();
            $this->handle();

            $last_tick = $time;

            if ($singular) {
                break;
            }
        }
    }

    public function stop()
    {
        $this->run = false;
    }

    public static function __callStatic($name, $args)
    {
        $instance = static::instance();

        switch ($name) {
            case "run":
                $instance->run();
                return;
            case "stop":
                $instance->stop();
        }

        throw new \Exception('Method not defined.');
    }
}