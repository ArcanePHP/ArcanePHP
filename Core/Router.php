<?php

namespace Core;

use Core\Request;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use ReflectionMethod;

class Router
{


    public static array $finalRoutes = [];
    public static array $routesWithName = [];
    public static string $callAs;
    use RouterHelper;

    public function __construct()
    {
        self::$request = new Request();
        self::$method = self::$request->getMethod();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    public function callAs(string $name)
    {

        self::$routesWithName[$name] = self::$finalRoutes[self::$callAs];
        self::$routesWithName[$name]['url'] = REQUEST_SCHEME . '://' . HOST . '/' . self::$finalRoutes[self::$callAs]['url'];
        return true;
    }
    public static function get($route, $param = '', $middleware = false, $controller = false): self
    {
        self::testRouteAdder('get', $route, $param, $middleware, $controller);
        return self::getInstance();
    }


    public static function post($route, $param = '', $middleware = false, $controller = false): self
    {
        self::testRouteAdder('post', $route, $param, $middleware, $controller);
        return self::getInstance();
    }

    public static function put($route, $param = '', $middleware = false, $controller = false): self
    {
        self::testRouteAdder('put', $route, $param, $middleware, $controller);
        return self::getInstance();
    }
    public static function delete($route, $param = '', $middleware = false, $controller = false): self
    {
        self::testRouteAdder('delete', $route, $param, $middleware, $controller);
        return self::getInstance();
    }

    public function action(string $action): self
    {
        self::$finalRoutes[self::$callAs]['action'] = $action;
        return self::getInstance();
    }



    public static function testRouteAdder($requestMethod, $route, $param, $middleware = false, $controller = false)
    {

        $url = $route;
        $route = $route . ':' . $requestMethod;
        $route = preg_replace('/\//', '\\/', $route);
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z0-9-]+)', $route);
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);

        $route = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<\1>\d+)', $route);
        // $route = preg_replace('/\{([a-z]+)\}/', '(a-zs)', $route);
        // vd($route);
        $route = '/^' . $route . '$/i';
        //used for setting $namedroutes when callAs function is called 

        self::$callAs = $route;
        if ($url == 'shop/addtofav/{product_id}') {
            $url = 'shop/addtofav/{product_id}:post';
            $test = '/^shop/addtofav/1:post$/i';

            // vd($route);
            // vd($test);
            preg_match($route, $url, $matches);
            // vd($matches);
            // dd($url);
        }

        if ($middleware) {
            self::$finalRoutes[$route]['middleware'] = $middleware;
        }
        if (is_callable($param)) {
            self::$finalRoutes[$route]['callable'] = $param;
            return false;
        }

        self::$finalRoutes[$route]['method'] = $requestMethod;
        //Adds action
        if (isset($param[0]) && str_starts_with($param[0], '::')) {
            $str = explode('::', $param[0]);
            $action = $str[1];
            self::$finalRoutes[$route]['action'] = $action;
            // self::setFinalRoutes([$route, 'action'], $action);
        }

        //adds controller 
        if (isset($param[1]) && str_contains($param[1], 'Controller')) {
            self::$finalRoutes[$route]['controller'] = $param[1];
        }

        self::$finalRoutes[$route]['url'] =  preg_replace('/\{([a-z._]+)\}/', '', $url);

        preg_match('/\{([a-z._]+)\}/', $url, $matches);;
        if (isset($matches[1])) {
            self::$finalRoutes[$route]['required_column_with_url'] = $matches[1];
        } else {
            // vd($url);
        }


        if (isset($param['view'])) {
            self::$finalRoutes[$route]['view'] = $param['view'];
        }


        // vd(self::$finalRoutes);
    }


    public static function group(callable $callable, string $middleware = '', string $controller = ''):self
    {


        // dd($callable);
        $routes = self::$finalRoutes;
        $previous_router_length = count($routes);
        call_user_func($callable);
        $number_of_routers_to_deal_with = (count(self::$finalRoutes)) - $previous_router_length;
        $router_to_deal_with = array_slice(self::$finalRoutes, -$number_of_routers_to_deal_with);
        // vd($router_to_deal_with);
        foreach ($router_to_deal_with as $current_router_key => $current_router) {
            if ($controller && !array_key_exists('controller', $current_router)) {
                self::$finalRoutes[$current_router_key]['controller'] = $controller;
            }
            if ($middleware && !array_key_exists('middleware', $current_router)) {
                self::$finalRoutes[$current_router_key]['middleware'] = $middleware;
            }

            if (array_key_exists('0', $current_router)) {
                // dd($current_router);
                self::$finalRoutes[$current_router_key]['action'] = str_replace('::', '', $current_router[0]);
                unset(self::$finalRoutes[$current_router_key][0]);
            }
        }
        return self::getInstance();
    }
    public function view(string $view): self
    {

        // self::$finalRoutes[self::$callAs] = $view;
        self::$finalRoutes[self::$callAs]['view'] = $view;
        return self::getInstance();
    }
    public function controller(string $controller, string $method): self
    {
        self::$finalRoutes[self::$callAs]['controller'] = $controller;
        self::$finalRoutes[self::$callAs]['action'] = $method;


        return self::getInstance();
    }
    public function to(string $controller, string $method): self
    {
        self::$finalRoutes[self::$callAs]['controller'] = $controller;
        self::$finalRoutes[self::$callAs]['action'] = $method;


        return self::getInstance();
    }

    public  function prefix() {}


    private static function preg_replace($key)
    {
        // $route = preg_replace('/\//', '\\/', $key);
        $route = preg_replace('/\{([a-z0-9]+)\}/', '(?P<\1>[a-z0-9-]+)', $key);
        // $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);
        $route = '/^' . $key . ':' . Request::getMethod() . '$/i';

        return $route;
    }
    private static function setFinalRoutes(array $keys, array|string $value)
    {
        // dd(...$key);

        // $keys = ...$key;
        foreach ($keys as $key) {
        }
        // self::$finalRoutes[...$key]
    }
    private static function getFinalRoutes(string $key): array|bool
    {
        if (isset(self::$finalRoutes[$key])) {
            return self::$finalRoutes[$key];
        };
        return false;
    }
    public static function getAllUrl()
    {
        $transformedArray = array_map(function ($subArray) {
            return $subArray['url'];
        }, self::$routesWithName);
        return $transformedArray;
    }
    public static function matched($url)
    {

        $url = lcfirst($url);
        $routes = '';
        $method = Request::getMethod();

        // vd($method);
        // dd($routes);
        // dd(self::preg_replace($url));
        $currentUrl = self::preg_replace($url);
        $ptr = "/^shop\/addtofav\/(?P<product_id>\d+):(?P<action>.+)$/i";
        // vd($currentUrl);
        // dd(preg_match($ptr, $currentUrl, $matches));

        if ($matchedParam = self::getFinalRoutes($currentUrl)) {
            // dd($matchedParam);
            return $matchedParam;
        } else {
            $currentUrl = $url . ':' . $method;
        }
        // $ff = self::$finalRoutes[$currentUrl];
        // dd($ff);
        // vd(self::$finalRoutes);
        // dd($currentUrl);
        // if(
        foreach (self::$finalRoutes as $router => $params) {
            // pr(self::$staticRoutes);
            // pr($router);
            // dd($url);
            if (preg_match($router, $currentUrl, $matches)) {
                // pr($matches);
                foreach ($matches as $key => $values) {
                    // pr($key);
                    // pr($values);
                    if (is_string($key)) {
                        $params[$key] = $values;
                    }
                }
                self::$params = $params;
                return $params;
                // dd($params);
                // return true;
            }
        }
        // exit();
        return false;
    }


    public static function dispatch($url)
    {
        // dd(self::$finalRoutes);
        // return false ;
        // dd(self::$finalRoutes);
        self::getRoutesWithName();

        if ($matched_param = self::matched($url)) {
            if (isset($matched_param['callable'])) {
                $res = call_user_func($matched_param['callable']);
                if (is_array($res)) {
                    print_r($res);
                } else {
                    echo $res;
                }
                // dd($res);
                return false;
            }
            // if($)
            // vd(self::$params);
            // dd($GLOBALS['routes']);
            $allowedMethod = ($matched_param['method']);

            // dd($allowedMethod);
            $allowedMethod = explode('/', $allowedMethod);
            $method = self::$method;
            $allowedMethod = in_array($method, $allowedMethod);

            // vd($matched_param);
            if (!$allowedMethod) {
                if ($_ENV['DEBUG']) {
                    $error = [
                        'error' => true,
                        'message' => 'Method ' . $method . ' not allowed on ' . $url . '->index. Allowed method: ' . $allowedMethod
                    ];

                    return show($error)->on(0, file: 'errors/customError');
                }
                return false;
            }

            if (isset($matched_param['view']) && $view = $matched_param['view']) {
                // dd($matched_param);
                View::render($view . '.html');
            }
            $result = self::runMiddleWare($matched_param);
            if (!$result) {
                return false;
            }


            $controller = str_replace('-', '', ($matched_param['controller']) ?? 'view');
            // dd($controller);
            $controller = (self::convertToStudlyCaps($controller));

            if (!class_exists($controller)) {
                $controller = self::getNamespace() . $controller;
            }


            //check if controller class exist ; 

            if ($controller) {
                //check is method callable 
                $action = $matched_param['action'] ?? 'view';
                $action = (self::convertToCamelcase($action));
                $controller_object = new $controller();

                if (is_callable([$controller_object, $action])) {
                    self::callController($controller_object, $action);
                } else {
                    // echo  "method $action not found in $controller";
                    throw new \Exception("method $action not found in $controller");
                }
            } else {
                // echo "controller $controller  does not exist";
                throw new \Exception("controller $controller  does not exist");
            }
        } else {
            // echo 'router not found';
            // dd($url);
            throw new \Exception($url . '  router not found', 404);
        }
    }


    private static function runMiddleWare(array $params)
    {
        $middleware = '';
        if (array_key_exists('middleware', $params)) {
            $middleware = $params['middleware'] ? $params['middleware'] : $params['middleware'][0];
        } else {
            return true;
        }


        if (class_exists($middleware)) {
            $middleware = new $middleware();
            return $middleware->handle();

            // ($this->callController($middleware, 'handle'));
        } else {
            return true;
        }
    }

    public static function getAllRoutes()
    {
        return self::$finalRoutes;
    }
    public static function getRoutesWithName()
    {
        $filePath = ROOT . '/Cache/routes_with_name.php';

        if (isset($_SERVER['REQUEST_METHOD'])) {
            $routes = self::$routesWithName;
            $routesExport = var_export($routes, true);
            file_put_contents($filePath, "<?php return $routesExport;");
        } else {
            $routes = include($filePath);
        }
        // Define the file path to save the routes
        // dd($routes);

        return $routes;
    }
    // public static  function getRoutesWithName()
    // {
    //     // Setup the cache
    //     CacheManager::setDefaultConfig(new ConfigurationOption([
    //         "path" => ROOT . '/../Cache',
    //     ]));
    //     $cache = CacheManager::getInstance('files');

    //     // Try to get the item from cache
    //     $cacheKey = 'routes_with_name';
    //     $cachedRoutes = $cache->getItem($cacheKey);

    //     if (!$cachedRoutes->isHit()) {
    //         echo 'non cached';
    //         // Cache miss, get the routes and save to cache
    //         $routes = self::$routesWithName;

    //         $cachedRoutes->set($routes)->expiresAfter(3600); // Cache for 1 hour
    //         $cache->save($cachedRoutes);
    //     } else {
    //         echo 'cached';
    //     }

    //     return $cachedRoutes->get();
    // }
    public static function callController($controller_object, $action)
    {
        $r = new ReflectionMethod($controller_object, $action);
        // dd($action);

        $params = self::$params;
        $actionParams = [];

        if (isset($params['view'])) {
            // dd('hji');

            $controller_object->$action($params['view']);
        } else if ($r->getNumberOfParameters() > 0) {
            // dd('hji');

            foreach ($r->getParameters() as $parameter) {
                $paramName = $parameter->getName();
                $paramType = $parameter->getType();
                // dd($paramName);

                if ($paramType && !$paramType->isBuiltin()) {
                    $className = $paramType->getName();
                    $actionParams[] = new $className();
                } else if (isset($params[$paramName])) {
                    $actionParams[] = $params[$paramName];
                } else {
                    $actionParams[] = $paramName;
                }
            }

            // dd
            // dd($actionParams);
            $controller_object->$action(...$actionParams);
        } else if (isset($params['action'])) {
            $res = $controller_object->$action($params['action']);

            // dd($res);
        } else {
            //    dd($controller_object->$action);
            // \call_user_method($action, $controller_object);
            $controller_object->$action();
            // \call_user_method
        }

        unset($controller_object);
    }
    private function __clone() {}

    // Prevent unserializing of the instance
    public function __wakeup() {}
}
