<?php
use Hedronium\Torus\Eventful;
use Hedronium\Torus\Event;
use Hedronium\Torus\EventLoop;
use Hedronium\Torus\Timeout;
use Hedronium\Torus\Interval;
use Hedronium\Torus\Pollable;
use Mockery as M;

class EventLoopTest extends PHPUnit_Framework_TestCase
{
    protected $loop = null;

    public function setUp()
    {
        $this->loop = new EventLoop(false);
    }

    public function tearDown()
    {
        M::close();
    }

    public function testInstanceOfInstance()
    {
        $instance = EventLoop::instance();
        $this->assertInstanceOf(EventLoop::class, $instance);
    }

    public function testInstanceOfAfterInstantiation()
    {
        $instantiation = new EventLoop;
        $staticMethod = EventLoop::instance();

        $this->assertSame($instantiation, $staticMethod);
    }

    public function testInstanceOfDoubleInstance()
    {
        $instantiation = EventLoop::instance();
        $staticMethod = EventLoop::instance();

        $this->assertSame($instantiation, $staticMethod);
    }

    public function testAutoRegistrationConstruction()
    {
        $loop = new EventLoop;
        $this->assertTrue($loop->shouldAutoRegister());
    }

    public function testNoAutoRegistrationConstruction()
    {
        $loop = new EventLoop(false);
        $this->assertFalse($loop->shouldAutoRegister());
    }

    public function testRegister()
    {
        $eventful = M::mock(Eventful::class)
            ->shouldReceive('register')
            ->once()
            ->with($this->loop)
            ->andReturn(null)
            ->mock();

        $this->loop->register($eventful);
    }

    public function testRegisterNonEventful()
    {
        $this->setExpectedException(PHPUnit_Framework_Error::class);
        $this->loop->register(new stdClass);
    }

    public function testCommit()
    {
        $eventful = M::mock(Eventful::class);

        $eventful = $eventful->shouldReceive('setPollableObject')
            ->once()
            ->andReturnNull()
            ->mock();

        $eventful = $eventful->shouldReceive('boot')
            ->once()
            ->withNoArgs()
            ->andReturnNull()
            ->mock();

        $pollable = $this->loop->commit($eventful);
    }

    public function testCommitNotEventful()
    {
        $this->setExpectedException(PHPUnit_Framework_Error::class);
        $this->loop->commit(new stdClass);
    }

    public function testIsRegistered()
    {
        $eventful = M::mock(Eventful::class);

        $eventful = $eventful->shouldReceive('setPollableObject', 'boot')
            ->once()
            ->andReturnNull()
            ->mock();

        $pollable = $this->loop->commit($eventful);

        $eventful->shouldReceive('getPollableObject')
            ->once()
            ->andReturn($pollable)
            ->mock();

        $this->assertTrue($this->loop->isRegistered($eventful));
    }

    public function testIsNotRegistered()
    {
        $eventful = M::mock(Eventful::class);
        $pollable = M::mock(Pollable::class);
        $eventful->shouldReceive('getPollableObject')
            ->once()
            ->andReturn($pollable)
            ->mock();

        $this->assertFalse($this->loop->isRegistered($eventful));
    }

    public function testIsNotRegisteredNotEventful()
    {
        $this->setExpectedException(PHPUnit_Framework_Error::class);
        $this->loop->isRegistered(new stdClass);
    }

    public function testRemove()
    {
        $eventful = M::mock(Eventful::class);

        $eventful = $eventful->shouldReceive('setPollableObject', 'boot')
            ->once()
            ->andReturnNull()
            ->mock();

        $pollable = $this->loop->commit($eventful);

        $eventful->shouldReceive('getPollableObject')
            ->andReturn($pollable)
            ->mock();

        $this->loop->remove($eventful);

        $this->assertFalse($this->loop->isRegistered($eventful));
    }

    public function testRemoveNotEventful()
    {
        $this->setExpectedException(PHPUnit_Framework_Error::class);
        $this->loop->remove(new stdClass);
    }

    public function testPushEvent()
    {
        $pollable = M::mock(Pollable::class);
        $event_type = 'TEST';
        $event_data = 'TEST DATA';
        $event_priority = 0;

        $event = $this->loop->pushEvent($pollable, $event_type, $event_data, $event_priority);
        $queue = $this->loop->getQueue();
        $queue->setExtractFlags(3);

        $this->assertInstanceOf(Event::class, $event);

        list($returned_event, $returned_priority) = array_values($queue->top());

        $this->assertSame($returned_event, $event);
        $this->assertSame($returned_event->getData(), $event_data);
        $this->assertSame($returned_event->getType(), $event_type);
    }

    public function testPushEventPriority()
    {
        $pollable_a = M::mock(Pollable::class);
        $pollable_b = M::mock(Pollable::class);
        $pollable_c = M::mock(Pollable::class);

        $event_a = $this->loop->pushEvent($pollable_a, 'TEST', '', 10);
        $event_b = $this->loop->pushEvent($pollable_b, 'TEST', '', 300);
        $event_c = $this->loop->pushEvent($pollable_c, 'TEST', '', 20);

        $queue = $this->loop->getQueue();
        $this->assertSame($queue->top(), $event_b);
    }

    public function testPoll()
    {
        $eventful = M::mock(Eventful::class)
            ->shouldReceive('setPollableObject', 'boot')
            ->once()
            ->andReturnNull()
            ->getMock();

        $pollable = M::mock(Pollable::class)
            ->shouldReceive('poll')
            ->once()
            ->withNoArgs()
            ->andReturnNull()
            ->getMock();

        $factory = function () use ($pollable) {
            return $pollable;
        };

        $this->loop->setPollableFactory($factory);

        $this->loop->commit($eventful);

        $this->loop->run(0, true);
    }

    public function testHandle()
    {
        $pollable = M::mock(Pollable::class)
            ->shouldReceive('handle', 'poll')
            ->once()
            ->andReturnNull()
            ->getMock();

        $eventful = M::mock(Eventful::class)
            ->shouldReceive('setPollableObject', 'boot')
            ->once()
            ->andReturnNull()
            ->getMock();

        $factory = function () use ($pollable) {
            return $pollable;
        };

        $this->loop->setPollableFactory($factory);

        $this->loop->commit($eventful);

        $this->loop->pushEvent($pollable, 'TEST', 'TEST');

        $this->loop->run(0, true);
    }
}