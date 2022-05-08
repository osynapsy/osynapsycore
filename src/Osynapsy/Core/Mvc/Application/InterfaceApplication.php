<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Core\Mvc\Application;

use Osynapsy\Core\Kernel\Route;
use Osynapsy\Core\Http\Request;
use Osynapsy\Core\Http\Response\Base as Response;
use Osynapsy\Core\Database\Driver\InterfaceDbo;

interface InterfaceApplication
{
    public function __construct(Route &$route, Request &$request);

    public function getDb(int $key = 0) : InterfaceDbo;

    public function getDbFactory() : \Osynapsy\Db\DbFactory;

    public function getRequest($key = null);

    public function getResponse() : Response;

    public function getRoute() : Route;

    public function run();

    public function runAction() : string;

    public function setResponse(Response $response);
}
