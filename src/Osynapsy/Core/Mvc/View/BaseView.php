<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Core\Mvc\View;

use Osynapsy\Core\Kernel;
use Osynapsy\Core\Mvc\Controller\InterfaceController;
use Osynapsy\Core\Mvc\Model\InterfaceModel;
use Osynapsy\Core\Mvc\View\InterfaceView;
use Osynpasy\Core\Database\Driver\InterfaceDbo;

abstract class BaseView implements InterfaceView
{
    private $controller;

    public function __construct(InterfaceController $controller, $title = null)
    {
        $this->controller = $controller;
        if (!empty($title)) {
            $this->setTitle($title);
        }
    }

    protected function add($part)
    {
        $this->getController()->getResponse()->send($part);
        if (is_object($part)) {
            return $part;
        }
    }

    public function addCss($path)
    {
        $this->getController()->getResponse()->addCss($path);
    }

    public function addCssLibrary($path)
    {
        $this->addCss(sprintf('/assets/osynapsy/%s/%s', Kernel::VERSION, $path));
    }

    public function addJs($path)
    {
        $this->getController()->getResponse()->addJs($path);
    }

    public function addJsCode($code)
    {
        $this->getController()->getResponse()->addJsCode($code);
    }

    public function addJsLibrary($path)
    {
        $this->addJs(sprintf('/assets/osynapsy/%s/%s', Kernel::VERSION, $path));
    }

    public function addMeta($property ,$content)
    {
        $meta = new \Osynapsy\Html\Tag('meta');
        $meta->att(['property' => $property, 'content' => $content]);
        $this->getController()->getResponse()->addContent($meta, 'meta');
    }

    public function addStyle($style)
    {
        $this->getController()->getResponse()->addStyle($style);
    }

    public function get()
    {
        return $this->init();
    }

    public function getController() : InterfaceController
    {
        return $this->controller;
    }

    public function getModel() : InterfaceModel
    {
        return $this->getController()->getModel();
    }

    public function getDb() : InterfaceDbo
    {
        return $this->getController()->getDb();
    }

    public function setTitle($title)
    {
        $this->getController()->getResponse()->addContent($title, 'title');
    }

    public function __toString()
    {
        return $this->get().'';
    }

    abstract public function init();
}
