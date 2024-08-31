<?php
namespace Core ;

use PDO;

/**
 * @mixin PDO; 
 */

class DB  {

    private PDO $pdo ;
    public function __construct($config){
        $defaultOptions = [
            PDO::ATTR_EMULATE_PREPARES =>false,
            PDO::ATTR_DEFAULT_FETCH_MODE =>PDO::FETCH_ASSOC
        ];
        try {
            $this->pdo =  new PDO(Config::DB_DRIVER . ':host=' . $config['DB_HOST'] . ';dbname=' . $config['DB_NAME'], Config::DB_USER, Config::DB_PASSWORD,$config['options']??$defaultOptions);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), $e->getCode());
        };

    }
    //this method is called when user calls an undefined function
    public  function __call($name, $arguments)
    {
    return call_user_func_array([$this->pdo,$name],$arguments);
    }
}