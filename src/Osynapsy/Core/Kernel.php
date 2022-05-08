<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Core;

use Osynapsy\Core\Http\Request;
use Osynapsy\Core\Kernel\Loader;
use Osynapsy\Core\Kernel\Router;
use Osynapsy\Core\Kernel\KernelException;
use Osynapsy\Core\Kernel\Error\Dispatcher as ErrorDispatcher;

/**
 * The Kernel is the core of Osynapsy
 *
 * It init Http request e translate it in response
 *
 * @author Pietro Celeste <p.celeste@osynapsy.org>
 */
class Kernel
{
    const VERSION = '0.8-DEV';
    const DEFAULT_APP_CONTROLLER = '\\Osynapsy\\Mvc\\Application';
    const DEFAULT_ASSET_CONTROLLER = 'Osynapsy\\Assets\\Loader';

    public $route;
    public $router;
    public $request;
    public $requestUri;
    public $controller;
    public $appController;
    private $loader;
    private $composer;

    /**
     * Kernel costructor
     *
     * @param string $instanceConfigurationFile path of the instance configuration file
     * @param object $composer Instance of composer loader
     */
    public function __construct($instanceConfigurationFile, $composer = null)
    {
        $this->composer = $composer;
        $this->loader = new Loader($instanceConfigurationFile);
        $this->request = new Request($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER);
        $this->request->set('app.parameters', $this->loadConfig('parameter', 'name', 'value'));
        $this->request->set('env', $this->loader->get());
        $this->request->set('app.layouts', $this->loadConfig('layout', 'name', 'path'));
        $this->request->set('observers', $this->loadConfig('observer', '@value', 'subject'));
        $this->request->set('listeners', $this->loadConfig('listener', '@value', 'event'));
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getVersion()
    {
        return self::VERSION;
    }

    private function loadConfig($key, $name, $value)
    {
        $array = $this->loader->search($key);
        $result = [];
        foreach($array as $rec) {
            $result[$rec[$name]] = $rec[$value];
        }
        return $result;
    }

    protected function loadRequestUri()
    {
        $this->requestUri = strtok(filter_input(INPUT_SERVER, 'REQUEST_URI'), '?');
    }

    /**
     * Load in router object all route of application present in config file
     */
    private function loadRoutes()
    {
        $this->router = new Router($this->request);
        $this->router->addRoute('OsynapsyAssetsManager', '/assets/osynapsy/'.self::VERSION.'/{*}', self::DEFAULT_ASSET_CONTROLLER, '', 'Osynapsy');
        $applications = $this->loader->get('app');
        if (empty($applications)) {
            throw $this->raiseException(1001, 'No app configuration found');
        }
        foreach (array_keys($applications) as $applicationId) {
            $routes = $this->loader->search('route', "app.{$applicationId}");
            foreach ($routes as $route) {
                if (!isset($route['path'])) {
                    continue;
                }
                $id = isset($route['id']) ? $route['id'] : uniqid();
                $uri = $route['path'];
                $controller = $route['@value'];
                $template = !empty($route['template']) ? $this->request->get('app.layouts.'.$route['template']) : '';
                $this->router->addRoute($id, $uri, $controller, $template, $applicationId, $route);
            }
        }
    }

    protected function findActiveRoute()
    {
        $this->route = $this->router->dispatchRoute($this->requestUri);
        $this->getRequest()->set('page.route', $this->route);
    }

    /**
     * Run process to get response starting to request uri
     *
     * @param string $requestUri is Uri requested from
     * @return string
     */
    public function run()
    {
        try {
            $this->loadRequestUri();
            $this->loadRoutes();
            $this->findActiveRoute();
            $this->validateRouteController();
            return $this->runApplication($this->route, $this->request);
        } catch (\Exception $exception) {
            $errorDispatcher = new ErrorDispatcher($this->getRequest());
            return $errorDispatcher->dispatchException($exception);
        } catch (\Error $error) {
            $errorDispatcher = new ErrorDispatcher($this->getRequest());
            return $errorDispatcher->dispatchError($error);
        }
    }

    protected function raiseException($code, $message, $submessage = '')
    {
        $exception = new KernelException($message, $code);
        if (!empty($submessage)) {
            $exception->setInfoMessage($submessage);
        }
        return $exception;
    }

    public function runApplication($route, $request)
    {
        $reqApp = $request->get(sprintf("env.app.%s.controller", $route->application));
        //If isn't configured an app controller for current instance load default App controller
        $applicationClass = empty($reqApp) ? self::DEFAULT_APP_CONTROLLER : str_replace(':', '\\', $reqApp);
        $application = new $applicationClass($route, $request);
        $application->run();
        return (string) $application->runAction();
    }


    protected function validateRouteController()
    {
        if (!empty($this->route) && $this->route->controller) {
            return;
        }
        throw $this->raiseException(404, "Page not found", sprintf(
            'THE REQUEST PAGE NOT EXIST ON THIS SERVER <br><br> %s',
            $this->request->get('server.REQUEST_URI')
        ));
    }
}
