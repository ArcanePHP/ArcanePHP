<?php

namespace Core;

trait StaticMethods
{


    public static function getMethod(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }
    public static function StaticGet($route, $param = [], $middleware = false): void
    {
        if (self::getMethod() == 'get') {
            // $this->addRoutes($route, $param, $middleware);
            self::addRoutesStatic($route, $param, $middleware);
        }
    }

    public function __construct()
    {
        $this->request = new Request();
        $this->method = $this->request->getMethod();
    }

    public static function crud(string $controller, array $methods = [])
    {
        $controller =  explode('\\', $controller);
        $controller = end($controller);
        $resourceName = strtolower(str_replace('Controller', ' ', $controller));

        if (!$methods) {
            $methods = [
                'create' => ['url' => $resourceName . '/create', 'method' => 'post'],
                'read' => ['url' => $resourceName . '/{id}', 'method' => 'get'],
                'update' => ['url' =>  $resourceName . '/update/{id}', 'method' => 'post'],
                'delete' => ['url' => "$resourceName/delete/{id}", 'method' => 'post'],
            ];
        }

        foreach ($methods as $method => $resource) {

            // $this->addRoutes($route, $param);
            $url = $resource['url'];
            // vd($url);
            self::addRoutesStatic($url);
        }
        vd(self::$staticRoutes);
    }


    public static function addRoutesStatic($route, $param = [], $middleware = false): void
    {
        // vd($middleware);
        // vd($route);
        $route = preg_replace('/\//', '\\/', $route);
        // vd($route);
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z0-9-]+)', $route);
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);
        // vd($route);
        $route = '/^' . $route . '$/i';
        // $this->routes[$route] = $param;
        self::$staticRoutes[$route] = $param;
        if ($middleware) {
            self::$staticRoutes[$route]['middleware'] = $middleware;
        }
    }

    public function addRoutes($route, $param = [], $middleware = false): void
    {
        // vd($middleware);
        // vd($route);
        $route = preg_replace('/\//', '\\/', $route);
        // vd($route);
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z0-9-]+)', $route);
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);
        // vd($route);
        $route = '/^' . $route . '$/i';
        $this->routes[$route] = $param;
        if ($middleware) {
            $this->routes[$route]['middleware'] = $middleware;
        }
    }

    public function getAllRoutes(): array
    {

        return $this->routes;
    }

    public function dispatch($url)
    {

        if ($this->matched($url)) {
            $result = $this->runMiddleWare($this->params);
            if (!$result) {
                return false;
            }

            $controller = str_replace('-', '', ($this->params['controller']) ?? 'View');
            $controller = ($this->convertToStudlyCaps($controller));

            if (class_exists($controller)) {
            } else {

                $controller = $this->getNamespace() . $controller;
            }
            //check if controller class exist ; 

            if ($controller) {
                //check is method callable 
                $action = $this->params['action'] ?? 'view';
                $action = ($this->convertToCamelcase($action)) . 'Action';
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
            throw new \Exception($url . 'router not found', 404);
        }
    }
    //float(9.4890594482421875E-5)
    public function callController($controller_object, $action)
    {
        $r = new ReflectionMethod($controller_object, $action);
        $params = $this->params;
        $actionParams = [];

        if (isset($params['View'])) {
            $controller_object->$action($params['View']);
        } else if ($r->getNumberOfParameters() > 0) {
            foreach ($r->getParameters() as $parameter) {
                $paramName = $parameter->getName();
                $paramType = $parameter->getType();

                if ($paramType && !$paramType->isBuiltin()) {
                    $className = $paramType->getName();
                    $actionParams[] = new $className();
                } else if (isset($params[$paramName])) {
                    $actionParams[] = $params[$paramName];
                } else {
                    $actionParams[] = $paramName;
                }
            }

            $controller_object->$action(...$actionParams);
        } else if (isset($params['action'])) {
            $controller_object->$action($params['action']);
        }

        unset($controller_object);
        exit();
    }
    //float(9.2029571533203125E-5)
    public  function callControllerb($controller_object, $action)
    {


        $r  = new ReflectionMethod($controller_object, $action);
        if (array_key_exists('View', $this->params)) {
            $controller_object->$action($this->params['View']);
            unset($controller_object);
            exit();
        } elseif (count($r->getParameters())) {
            //if calling controller methods require any parameters
            $calledControllerParameters = $r->getParameters();
            // vd($calledControllerParameters);
            $objectstopass = [];
            foreach ($calledControllerParameters as $parameter) {
                $methodtypeisobject = $parameter->getType() ?? null;
                if ($methodtypeisobject) {
                    $methodsClassName = ($methodtypeisobject->getName());
                    $requiredObj = new $methodsClassName();
                    // vd($requiredObj);
                    array_push($objectstopass, $requiredObj);
                } else {
                    //if parameter is defined as any controller or action ...
                    $requiredParam = '';
                    if (array_key_exists($parameter->name, $this->params)) {
                        $requiredParam = ($this->params[$parameter->name]);
                    } else {
                        $dataDefinedWhileSettingRouter = $parameter->name;
                        $requiredParam = $dataDefinedWhileSettingRouter;
                    }

                    // $controller_object->$action($requiredParam);
                    array_push($objectstopass, $requiredParam);
                }
            }
            // vd($action);
            // vd($controller_object);
            $controller_object->$action(...$objectstopass);
        } else {
            if (isset($this->params['action'])) {
                $controller_object->$action($this->params['action']);
            }
            exit();
        }
    }


    public function matched($url): bool
    {
        $url = lcfirst($url);

        foreach ($this->routes as $router => $params) {
            // pr($this->routes);
            // pr($router);
            // pr($params);
            if (preg_match($router, $url, $matches)) {
                // pr($matches);
                foreach ($matches as $key => $values) {
                    // pr($key);
                    // pr($values);
                    if (is_string($key)) {
                        $params[$key] = $values;
                    }
                }
                $this->params = $params;
                return true;
            }
        }

        return false;
    }



    public function getAllParams(): array
    {

        return $this->params;
    }
    protected function convertToCamelcase(string $string): string
    {
        return lcfirst($this->convertToStudlyCaps($string));
    }
    protected function convertToStudlyCaps(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }
    public function getNamespace(): string
    {
        $namespace = 'App\Controllers\\';
        if (array_key_exists('namespace', $this->params)) {
            $namespace .= $this->params['namespace'] . '\\';
        }

        return $namespace;
    }
    private  function runMiddleWare(array $params)
    {
        $middleware = '';
        if (array_key_exists('middleware', $params)) {
            $middleware = $params['middleware'] ? $params['middleware'] : $params['middleware'][0];
        } else {
            return true;
        }


        if (class_exists($middleware)) {
            $middleware = new $middleware();
            return  $middleware->handle();

            // ($this->callController($middleware, 'handle'));
        } else {
            return true;
        }
    }


    public  function group(callable $callable, string $middleware = '', string $controller = '')
    {
        $routes = $this->routes;
        $previous_router_length = count($routes);
        call_user_func($callable);
        $number_of_routers_to_deal_with = (count($this->routes)) - $previous_router_length;
        $router_to_deal_with = array_slice($this->routes, -$number_of_routers_to_deal_with);
        foreach ($router_to_deal_with as $current_router_key => $current_router) {
            if ($controller && !array_key_exists('controller', $current_router)) {
                $this->routes[$current_router_key]['controller'] = $controller;
            }
            if ($middleware && !array_key_exists('middleware', $current_router)) {
                $this->routes[$current_router_key]['middleware'] = $middleware;
            }

            if (array_key_exists('0', $current_router)) {
                $this->routes[$current_router_key]['action'] = str_replace('::', '', $this->routes[$current_router_key][0]);
                unset($this->routes[$current_router_key][0]);
            }
        }
    }

}
