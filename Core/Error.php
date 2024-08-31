<?php
namespace Core ;

class Error {
    public static function errorHandler($level,$message,$file,$line){
        if(error_reporting()!==0){
            throw new \ErrorException($message,0,$level,$file,$line);
        }
    }
    public static function exceptionHandler($exception)  {
        $code = $exception->getCode();
        if($code !==404){
            $code = 505;
        }
        if(Config::SHOW_ERRORS){

            echo "
            <h1>Fatal error</h1>        
                                        <p>Uncaught exception :{getclass($exception)}</p>
            <p>Message :{$exception->getMessage()}</p>
            <p>Stack trace :{$exception->getTraceAsString()}</p>
            <p>Thrown in  :{$exception->getFile()} on line : {$exception->getLine()}</p>
            
                    ";
        }else{
        $log = ROOT.'/logs/'.date('Y-m-d').'.txt';    
        ini_set('error_log',$log);
        $message = "Uncaught exception : ".get_class($exception)."'";
        $message .= "with message '". $exception->getMessage() ."'";
        $message .= "\n Stack trace :". $exception->getTraceAsString();
        $message .= "\n Thrown in '". $exception->getFile() ."' on line " . $exception->getLine();
        error_log($message);
        View::renderTemplate("Error/$code.html");
        
        }
    }
}