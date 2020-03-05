<?php

namespace Pimcore\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Contracts\EventDispatcher\Event as ContractsEvent;

class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function dispatch($event, $eventName = null)
    {
        if (\is_object($event)) {
            $eventName = $eventName ?? \get_class($event);
        } elseif (\is_string($event) && (null === $eventName || $eventName instanceof ContractsEvent || $eventName instanceof Event)) {
            if (Kernel::VERSION_ID >= 40300) {
                @trigger_error(sprintf(
                    'Calling the "%s::dispatch()" method with the event name as the first argument is deprecated since Symfony 4.3, pass it as the second argument and provide the event object as the first argument instead.',
                    EventDispatcherInterface::class
                ), E_USER_DEPRECATED);
            }

            [$eventName, $event] = [$event, $eventName ?? new Event()];
        } else {
            throw new \TypeError(sprintf(
                'Argument 1 passed to "%s::dispatch()" must be %s, %s given.',
                Kernel::VERSION_ID >= 40300 ? 'an object' : 'a string',
                EventDispatcherInterface::class, \is_object($event) ? \get_class($event) : \gettype($event)
            ));
        }

        return Kernel::VERSION_ID >= 40300
            ? $this->eventDispatcher->dispatch($event, $eventName)
            : $this->eventDispatcher->dispatch($eventName, $event);
    }

    public function addListener($eventName, $listener, $priority = 0)
    {
        return $this->eventDispatcher->addListener($eventName, $listener, $priority);
    }

    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        return $this->eventDispatcher->addSubscriber($subscriber);
    }

    public function removeListener($eventName, $listener)
    {
        return $this->eventDispatcher->removeListener($eventName, $listener);
    }

    public function removeSubscriber(EventSubscriberInterface $subscriber)
    {
        return $this->eventDispatcher->removeSubscriber($subscriber);
    }

    public function getListeners($eventName = null)
    {
        return $this->eventDispatcher->getListeners($eventName);
    }

    public function getListenerPriority($eventName, $listener)
    {
        return $this->eventDispatcher->getListenerPriority($eventName, $listener);
    }

    public function hasListeners($eventName = null)
    {
        return $this->eventDispatcher->hasListeners($eventName);
    }

    /**
     * Proxies all method calls to the original event dispatcher.
     */
    public function __call($method, $arguments)
    {
        return $this->eventDispatcher->{$method}(...$arguments);
    }

    public function __get($property)
    {
        return $this->eventDispatcher->$property ?? null;
    }

    public function __isset($property)
    {
        return isset($this->eventDispatcher->$property);
    }

    public function __set($property, $value)
    {
        $this->eventDispatcher->$property = $value;
    }
}
