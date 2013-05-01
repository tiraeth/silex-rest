<?php

/*
 * This file is part of the Silex REST package.
 *
 * (c) Marcin Chwedziak <marcin@chwedziak.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mach\Silex\Rest;

use Silex\Application;

class Resource
{
    protected $app;
    protected $path;
    protected $idVariable;
    protected $routes;

    public function __construct(Application $app, $path, $controller = null, $idVariable = 'id')
    {
        if (is_object($controller)) {
            $controllerClass = static::underscoreDot(get_class($controller));
            
            $app[$controllerClass] = $app->share(function() use ($controller) {
                return $controller;
            });

            $controller = $controllerClass;
        }

        $this->app = $app;
        $this->path = $path;
        $this->idVariable = $idVariable;
        $this->routes = array();

        $this->prefix = substr($path, 1);
        $this->prefix = str_replace('/{' . $idVariable . '}', '', $this->prefix);
        $this->prefix = str_replace('/', '_', $this->prefix);

        if ($controller !== null) {
            $this->routes['cget'] = $this->cget(sprintf('%s:%s', $controller, $app['rest.methods.cget']));
            $this->routes['post'] = $this->post(sprintf('%s:%s', $controller, $app['rest.methods.post']));
            $this->routes['get'] = $this->get(sprintf('%s:%s', $controller, $app['rest.methods.get']));
            $this->routes['put'] = $this->put(sprintf('%s:%s', $controller, $app['rest.methods.put']));
            $this->routes['patch'] = $this->patch(sprintf('%s:%s', $controller, $app['rest.methods.patch']));
            $this->routes['delete'] = $this->delete(sprintf('%s:%s', $controller, $app['rest.methods.delete']));
        }
    }

    protected function itemPath()
    {
        return $this->path . '/{' . $this->idVariable . '}';
    }

    public function subresource($path, $controller = null, $idVariable = null)
    {
        if ($idVariable === null) {
            $idVariable = $this->idVariable . 'd';
        }
        
        return new Resource($this->app, $this->itemPath() . $path, $controller, $idVariable);
    }

    public function route($routeName)
    {
        if (!array_key_exists($routeName, $this->routes)) {
            return false;
        }
        
        return $this->routes[$routeName];
    }

    public function cget($controller)
    {
        $this->routes['cget'] = $this->app
            ->get($this->path, $controller)
            ->bind($this->prefix . '_cget');

        return $this;
    }

    public function post($controller)
    {
        $this->routes['post'] = $this->app
            ->post($this->path, $controller)
            ->bind($this->prefix . '_post');

        return $this;
    }

    public function get($controller)
    {
        $this->routes['get'] = $this->app
            ->get($this->itemPath(), $controller)
            ->bind($this->prefix . '_get');

        return $this;
    }

    public function put($controller)
    {
        $this->routes['put'] = $this->app
            ->put($this->itemPath(), $controller)
            ->bind($this->prefix . '_put');

        return $this;
    }

    public function patch($controller)
    {
        $this->routes['patch'] = $this->app
            ->match($this->itemPath(), $controller)
            ->method('PATCH')
            ->bind($this->prefix . '_patch');

        return $this;
    }

    public function delete($controller)
    {
        $this->routes['delete'] = $this->app
            ->delete($this->itemPath(), $controller)
            ->bind($this->prefix . '_delete');

        return $this;
    }

    public function before($routeName, $closure)
    {
        if (array_key_exists($routeName, $this->routes)) {
            $this->routes[$routeName]->before($closure);
        }

        return $this;
    }

    public function after($routeName, $closure)
    {
        if (array_key_exists($routeName, $this->routes)) {
            $this->routes[$routeName]->after($closure);
        }

        return $this;
    }

    public function assertId($constraint)
    {
        $routesWithId = array('get', 'put', 'delete', 'patch');

        foreach ($this->routes as $routeName => $route) {
            if (in_array($routeName, $routesWithId)) {
                $route->assert($this->idVariable, $constraint);
            }
        }

        return $this;
    }

    public function convert($variable, $closure)
    {
        $routesWithId = array('get', 'put', 'delete', 'patch');

        foreach ($this->routes as $routeName => $route) {
            if (in_array($routeName, $routesWithId)) {
                $route->convert($variable, $closure);
            }
        }

        return $this;
    }

    public static function underscoreDot($controllerClass)
    {
        $controllerClass = preg_replace('/([^A-Z])([A-Z])/', "$1_$2", $controllerClass);
        $controllerClass = str_replace('\\', '.', $controllerClass);
        
        return $controllerClass;
    }
}