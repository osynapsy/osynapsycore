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

use Osynapsy\Core\Mvc\Controller\InterfaceController;

/**
 * Public method of listener
 *
 * @author pietr
 */
interface InterfaceListener
{
    public function __construct(InterfaceController $controller);

    public function trigger(Event $event);
}
