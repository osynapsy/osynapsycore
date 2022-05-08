<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Core\Mvc\Model;

interface InterfaceModel
{
    public function __construct(Controller $controller);

    public function find();

    public function save();

    public function delete();
}
