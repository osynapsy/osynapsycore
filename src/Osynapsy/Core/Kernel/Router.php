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
 * The router class process all ruotes of the application and search the route
 * that match the request coming from the web
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class Router
{
    private $routes;
    private $requestRoute;
    private $matchedRoute;

    //Rispettare l'ordine
    private $patternPlaceholder = [
        '/'  => '\\/',
        //number
        '{i}' => '([\\d]+){1}',
        //Number option
        '{i*}' => '([\\d]*){1}',
        //word
        '{w}'=> '([\\w\-\.,]+){1}',
        //word
        '{w*}' => '([\\w\-\.,]*){1}',
        //all
        '{*}' => '(.*){1}',
        //all after /
        '{?}' => '([^\/]*)',
        //?????
        '{.}' => '([.]+){1}'
    ];

    public function __construct()
    {
        $this->routes = new RouteCollection();
        $this->matchedRoute = new Route('matched');
    }

    /**
     * Get the specified route
     *
     * @param type $key identify of the route
     * @return object
     */
    public function get($key)
    {
        return $this->routes->get($key);
    }

    /**
     * Add route to the collection;
     *
     * @param type $id  identify of the route
     * @param type $url uri of the route
     * @param type $controller controller of the ruote
     * @param type $templateId template of the ruote
     * @param type $application application of the ruote
     * @param array $attributes other attributes of the ruote
     */
    public function addRoute($id, $url, $controller, $templateId, $application, array $attributes = [])
    {
        $this->routes->addRoute($id, $url, $application, $controller, $templateId, $attributes);
    }

    /**
     * Process all the ruote of the collection and return the matched route
     *
     * @param type $uriToMatch ruote request from the web client;
     * @return object matched ruote;
     */
    public function dispatchRoute($uriToMatch)
    {
        $this->requestRoute = empty($uriToMatch) ? '/' : $uriToMatch;
        $routes = $this->routes->get('routes');
        if (!is_array($routes)) {
            return false;
        }
        //Get current request method;
        $requestMethod = strtolower(filter_input(\INPUT_SERVER, 'REQUEST_METHOD'));
        foreach($routes as $route) {
            //Check if url accept request http method;
            if (!is_null($route->acceptedMethods) && !in_array($requestMethod, $route->acceptedMethods)) {
               continue;
            }
            //Check if current route match request uri;
            $matchedRoute = $this->matchRoute($route);
            //If don't match (return value is false) jump next route;
            if ($matchedRoute === false) {
                continue;
            }
            //If weight is_null requested uri is exactly current uri. Don't search more.
            if (is_null($matchedRoute->weight)) {
                $this->matchedRoute = $matchedRoute;
                break;
            }
            //If weight of previous matched route is bigger of current matchedRoute then continue;
            if (!empty($this->matchedRoute) && $this->matchedRoute->weight > $matchedRoute->weight) {
                continue;
            }
            $this->matchedRoute = $matchedRoute;
        }
        return $this->getRoute();
    }

    /**
     * Process the ruote passed and check if match che request uri.
     *
     * @param type $route
     * @return mixed return false if ruote passed don't match the request uri then return the matched ruote;
     */
    private function matchRoute($route)
    {
        if (!substr_count($route->uri, '{')){
            return $route->uri === $this->requestRoute ? $route : false;
        }
        $output = $result = [];
        preg_match_all('/{.+?}/', $route->uri, $output);
        $braceParameters = array_merge(['/' => null] ,  array_flip($output[0]));
        array_walk(
            $braceParameters,
            function(&$value, $key, $placeholder) {
                if (array_key_exists($key, $placeholder)) {
                    $value = $placeholder[$key];
                    return;
                }
                $value = str_replace(['{','}'],['(',')'], $key);
            },
            $this->patternPlaceholder
        );
        $pattern = str_replace(
            array_keys($braceParameters),
            array_values($braceParameters),
            $route->uri
        );
        preg_match('/^'.$pattern.'$/', $this->requestRoute, $result);
        if (empty($result)) {
            return false;
        }
        array_shift($result);
        $route->parameters = $result;
        $route->weight = count($result);
        return $route;
    }

    /**
     * Return the matched uri
     *
     * @return type
     */
    public function getRoute()
    {
        return $this->matchedRoute;
    }
}
