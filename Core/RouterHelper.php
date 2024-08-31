<?php

namespace Core;


trait RouterHelper
{

    public static array $routes = [];
    protected static array $params = [];
    protected static Request $request;
    protected static string $method;
    private static $instance = null;

    public static function auth(string $controller, string $who, bool $login = true, bool $signup = false)
    {

        $ogController = $controller;
        $resourceName = $who;
        $methods = [];
        if ($login) {
            $methods['login'] = ['url' => 'login', 'method' => 'post/get'];
        }
        if ($signup) {
            $methods['signup'] = ['url' => 'signup', 'method' => 'post'];
        }
        $routes[] = '';
        foreach ($methods as $method => $resource) {
            $url = $resource['url'];

            $resource_method = $resource['method'];
            $name = $resourceName . '_' . $method;
            $route = REQUEST_SCHEME . '://' . HOST . '/' . $url;
            $routes[$name] = $route;

            $params = [
                'controller' => $ogController,
                'action' => $method,
                'method' => $resource_method
            ];
            self::addRoutesStatic($url, $params);
        }
        $GLOBALS['routes'][$resourceName] = $routes;
    }



    public static function crud(string $controller, array $methods = [], $middleware = false)
    {
        $ogController = $controller;
        $controller = explode('\\', $controller);
        $controller = end($controller);
        $resourceName = strtolower(str_replace('Controller', '', $controller));

        if (!$methods) {
            $methods = [
                '' => ['url' => $resourceName, 'method' => 'get'],
                'index' => ['url' => $resourceName, 'method' => 'get'],
                'create' => ['url' => $resourceName . '/create', 'method' => 'post', 'view' => $resourceName . '/create'],
                'store' => ['url' => $resourceName . '/store', 'method' => 'post', 'view' => $resourceName . '/store'],
                'show' => ['url' => $resourceName . '/{id}', 'method' => 'get', 'view' => "$resourceName/index"],
                'update' => ['url' => $resourceName . '/update/{id}', 'method' => 'post'],
                'delete' => ['url' => "$resourceName/delete/{id}", 'method' => 'post'],
                'edit' => ['url' => $resourceName . '/edit/{id}', 'method' => 'get', 'view' => $resourceName . '/edit']
            ];
        }

        $routes[] = '';
        foreach ($methods as $method => $resource) {
            // $url = $resource['url'] . "/$method";
            $url = $resource['url'];
            // dd($method);

            $resource_method = $resource['method'];

            if (!$method) {
                $name = '';
            } else {
                $name = $resourceName . '_' . $method;
            }

            $route = REQUEST_SCHEME . '://' . HOST . '/' . $url;
            $routes[$name] = $route;

            $params = [
                'controller' => $ogController,
                'action' => $method,
                'method' => $resource_method
            ];
            // vd($resource_method);
            self::testRouteAdder(requestMethod: $resource_method, route: $url, middleware: $middleware, controller: $controller, param: $params,);

            // self::addRoutesStatic(route: $url, param: $params, requestMethod: $resource_method);
            // vd($key);\\\
            // dd(selresource_methodf::$staticRoutes[$key]);
        }
        $GLOBALS['routes'][$resourceName] = $routes;

        // dd($GLOBALS['routes'][$resourceName]);
    }
  
    public static function getAllParams(): array
    {

        return self::$params;
    }

    protected static function convertToCamelcase(string $string): string
    {
        return lcfirst(self::convertToStudlyCaps($string));
    }
    protected static function convertToStudlyCaps(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }
    public static function getNamespace(): string
    {
        $namespace = 'App\Controllers\\';
        if (array_key_exists('namespace', self::$params)) {
            $namespace .= self::$params['namespace'] . '\\';
        }

        return $namespace;
    }

    
   
}
