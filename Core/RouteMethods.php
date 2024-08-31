<?php

namespace Core;

trait RouteMethods {

    public  function delete($route, $param)
    {

        if ($this->method == 'delete') {
            $this->addRoutes($route, $param);
        }
    }

    public function post($route, $param = []): void
    {
        if ($this->method == 'post') {
            $this->addRoutes($route, $param);
        }
    }


    public function get($route, $param = [], $middleware = false): void
    {
        if ($this->method == 'get') {
            $this->addRoutes($route, $param, $middleware);
        }
    }
}