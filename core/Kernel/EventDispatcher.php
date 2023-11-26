<?php
namespace Kernel;

use Contract\EventListenerInterface;
use Kernel\Exception\EventException;

/**
 * Event Dispatcher class
 * Subscribes event listeners, triggers events
 *
 * @author Igor Shvartsev (igor.shvartsev@gmail.com)
 */
class EventDispatcher
{
    /**
     * Every event item consists of 
     * event name  => list of listener instances  
     * @var array event subscribers 
     */
    protected $eventSubscribers = [];

    /**
     * Subscribe listener to event
     *
     * @param string $eventClassName name of event class
     * @param string $listenerClassName name of listener class
     * @throws EventException
     */
    public function subscribe($eventClassName, $listenerClassName)
    {
        $this->checkClassName($eventClassName);
        $this->checkClassName($listenerClassName);
        $listener = new $listenerClassName();

        if (!$listener instanceof EventListenerInterface) {
            throw new EventException(
                $listenerClassName . ' is not an instance of V9_Event_ListenerInterface' 
            );
        }

        $this->eventSubscribers[$eventClassName][] = $listener;
    }

    /**
     * Unsubscribe listener of the given event
     *
     * @param string $eventClassName name of event class
     * @param string $listenerClassName name of listener class
     */
    public function unsubscribe($eventClassName, $listenerClassName)
    {
        if (isset($this->eventSubscribers[$eventClassName])
            && is_array($this->eventSubscribers[$eventClassName])
        ) {
            $listeners = $this->eventSubscribers[$eventClassName];

            foreach ($listeners as $i => $listener) {
                if ($listener instanceof $listenerClassName) {
                    unset($listeners[$i]);
                }
            } 
        }
    }

    /**
     * Trigger event
     *
     * @param object $eventInstance should be instance of that event class
     * @param array $payload additional data passed to listener
     */
    public function trigger($eventInstance, $payload = [])
    {
       if (is_object($eventInstance)) {
            $eventClassName = get_class($eventInstance);

            if (isset($this->eventSubscribers[$eventClassName])) {
                $listenerList = $this->eventSubscribers[$eventClassName];

                if (is_array($listenerList)) {
                    foreach ($listenerList as $listener) {
                        $listener->handle($eventInstance, $payload);
                    }
                }
            }
       } 
    }

    /**
     * Initialize all events and their listeners from the array list
     *
     * @param array $eventSubscribers
     */
    public function provide($eventSubscribers = [])
    {
        if (is_array($eventSubscribers)) {
            foreach ($eventSubscribers as $eventClassName => $listenerList) {
                if (!is_array($listenerList)) {
                    $listenerList = [$listenerList];
                }

                foreach ($listenerList as $listenerClassName) {
                    $this->subscribe($eventClassName, $listenerClassName);
                }
            }
        }
    }

    /**
     * Get all list of the listeners for the given event
     *
     * @param string $eventClassName
     * @param boolean $onlyListenerClassNames if true returns only class names
     * @return array
     */
    public function getEventListeners($eventClassName, $onlyListenerClassNames = false)
    {
        $result = [];

        if (isset($this->eventSubscribers[$eventClassName])) {
            if ($onlyListenerClassNames) {
                foreach ($this->eventSubscribers[$eventClassName] as $listener) {
                    $result[] = get_class($listener);
                }
            } else {
                $result = $this->eventSubscribers[$eventClassName];
            }
        }

        return $result;
    }

    /**
     * Checks if exists class
     *
     * @param string $className
     * @throws EventException
     */
    protected function checkClassName($className) 
    {
        if (!is_string($className)) {
            throw new EventException('Class name is not a string');
        }

        if (!class_exists($className)) {
            throw new EventException('Class ' . $className . ' doesn\'t exist');
        }
    } 
}
