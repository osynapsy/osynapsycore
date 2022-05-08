<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Core\Mvc\Controller;

use Osynapsy\Core\Http\Request;
use Osynapsy\Core\Mvc\Application\InterfaceApplication;
use Osynapsy\Core\Mvc\Action\InterfaceAction;
use Osynapsy\Core\Database\Driver\InterfaceDbo;

interface InterfaceController
{
    public function __construct(Request $request = null, InterfaceApplication $application = null);

    public function getApp() : InterfaceApplication;

    public function getDb() : InterfaceDbo;

    public function getDispatcher();

    public function getModel() : InterfaceModel;

    public function getResponse();

    public function getRequest();

    public function setExternalAction(string $actionId, InterfaceAction $actionClass) : void;

    public function setModel(InterfaceModel $model);

    public function setView(InterfaceView $view);

    public function run($action, $parameters = []);
}