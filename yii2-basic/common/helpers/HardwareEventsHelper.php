<?php

namespace app\common\helpers;
use app\common\Controller;

/**
 * Class HardwareEventsHelper
 * @package app\common\helpers
 */
class HardwareEventsHelper
{

    const EVENT_INPUT = 0;
    const EVENT_OUTPUT = 1;

    /**
     * @var array
     */
    private $events;

    /**
     * HardwareEventsHelper constructor.
     * @param array $events
     */
    public function __construct(array $events)
    {
        $this->events = $events;
    }

    /**
     * Информация о входах/выходах клиента
     * @param $code
     * @return \Generator
     */
    public function getNextClientInfo($code)
    {
        $currentEventType = self::EVENT_OUTPUT;
        $currentEvent = null;

        if(isset($this->events[$code])) {

            foreach ($this->events[$code] as $event) {
                $eventType = self::getEventType($event);

                if ($currentEventType == self::EVENT_INPUT && $eventType == self::EVENT_OUTPUT &&
                        date('Y-m-d', strtotime($currentEvent['Time'])) == date('Y-m-d', strtotime($event['Time']))) {
                    yield [
                        self::EVENT_INPUT => $currentEvent,
                        self::EVENT_OUTPUT => $event,
                    ];
                }

                $currentEvent = $event;
                $currentEventType = $eventType;
            }
        }
    }

    /**
     * @param array $event
     * @return int
     */
    public static function getEventType(array $event)
    {
        return in_array($event['ControllerId'], Controller::inputIds()) ?
            self::EVENT_INPUT : self::EVENT_OUTPUT;
    }


}