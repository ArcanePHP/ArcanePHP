<?php

namespace Core;

class Request
{
    public function getPath()
    {

        $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) ?? false;
        return $path;
    }
    public static function getMethod(): string
    {


        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function allInputs()
    {

        $res =  match ($this->getMethod()) {
            'get' => $_GET,
            'post'   =>  $_POST
        };

        if($this->getMethod()=='post'){
            if(count($_POST)>0){
                return $_POST;
            }else{
                return  json_decode(file_get_contents("php://input"), true);
            }
        }else {
            return $res;
        }

    }
}
