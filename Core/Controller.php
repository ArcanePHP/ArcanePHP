<?php
namespace Core ;
class Controller {

public function __call($name, $arguments)
{
    $method= $name.'Action';
    if(method_exists($this,$method)){
        $this->before();
        call_user_func_array([$this,$method],$arguments);
        $this->after();
    }else{
        // echo "Method not found ($method) in ". get_class($this);
        throw new \Exception("Method not found ($method) in ".get_class($this));
    }
}

protected  function before(){
 }
protected function after(){

}
protected function getMethod() : string {
     return strtolower($_SERVER['REQUEST_METHOD']);

    
}
}

