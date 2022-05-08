<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Core\Kernel;

/**
 * Description of Route
 *
 * @author Peter
 */
class Route
{
    private $route = [
        'id' => null,
        'uri' => null,
        'application' => null,
        'controller' => null,
        'template' => null,
        'weight' => null,
        'acceptedMethods' => null
    ];

    public function __construct($id = '', $uri = '', $application = '', $controller = '', $template = '', array $attributes = [])
    {
        $this->id = empty($id) ? sha1($uri) : $id;
        $this->uri = $uri;
        $this->application = trim($application);
        $this->setController($controller);
        $this->template = $template;
        $this->route += $attributes;
        $this->setAcceptedMethods($this->methods);
    }

    public function __get($key)
    {
        return array_key_exists($key, $this->route) ? $this->route[$key] : null;
    }

    public function __set($key, $value)
    {
        $this->route[$key] = $value;
    }

    public function __toString()
    {
        return $this->id;
    }

    public function setController($controller)
    {
        $this->controller = trim(str_replace(':','\\',$controller));
    }

    public function setAcceptedMethods($methods)
    {
        if (empty($methods)) {
            return;
        }
        switch(gettype($methods)) {
            case 'string':
                $this->acceptedMethods = explode(',', strtolower($methods));
                break;
            case 'array':
                $this->acceptedMethods = $methods;
                break;
        }
    }
}
