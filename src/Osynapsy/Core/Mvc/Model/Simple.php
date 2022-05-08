<?php
namespace Osynapsy\Mvc\Model;

use Osynapsy\Mvc\InterfaceModel;
use Osynapsy\Mvc\Controller;

/**
 * Description of Simple
 *
 * @author Pietro
 */
abstract class Simple implements InterfaceModel
{
    protected $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
        $this->init();
    }

    public function getController() : Controller
    {
        return $this->controller;
    }

    public function getDb()
    {
        return $this->getController()->getDb();
    }

    public function find()
    {
    }

    public function save()
    {
    }

    public function init()
    {
    }

    public function delete()
    {
    }
}
