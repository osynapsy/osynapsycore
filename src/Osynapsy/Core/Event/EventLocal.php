<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Osynapsy\Core\Event;

/**
 * Description of EventLocal
 *
 * @author Pietro
 */
class EventLocal extends Event
{
    public function setEventId($eventId)
    {
        parent::setEventId(sprintf('%s\%s',get_class($this->origin), $eventId));
    }
}
