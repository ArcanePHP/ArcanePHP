<?php
 namespace Core;

use Core\DB;
use PDO;

 class Application {
public static DB $db; 

   public function __construct(protected Config $config)
   {
    $db = new DB($config->db ) ;
    static::$db = $db;

    
   }
   

   public static  function db():DB{
    return static::$db;
   }
   
 }