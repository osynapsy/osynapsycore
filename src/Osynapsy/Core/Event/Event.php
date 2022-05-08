<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Core\Event;

/**
 * Description of Event
 *
 * @author Peter
 */
class Event
{
    protected $origin;
    protected $eventId;

    public function __construct($eventId, $origin)
    {
        $this->origin = $origin;
        $this->setEventId($eventId);
    }

    public function getOrigin()
    {
        return $this->origin;
    }

    public function getNameSpace()
    {
        return get_class($this->origin);
    }

    public function getId()
    {
        return $this->eventId;
    }

    protected function setEventId($eventId)
    {
        $this->eventId = $eventId;
    }

    public function trigger()
    {
    }
}
