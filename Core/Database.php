<?php
namespace Core; 
use App\Config;
        trait Database {

            private static function connect(){
              
                try {
                    $dsn='mysql:host='.Config::DB_HOST.';dbname='.Config::DB_NAME;
                    $pdo = new \PDO($dsn,Config::DB_USER,Config::DB_PASSWORD);
                    $pdo->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION);
                    return $pdo;
                } catch (\Exception $th) {
                   echo 'Something went wrong on serverside';
                }
            }
            public function queryAction($sqlStatement,$data =[]){                      
                try {
                    $con = self::connect();
                    $stmt= $con->prepare($sqlStatement);  
                    $check = $stmt->execute($data);
                    
                    $check?($result = $stmt->fetchAll(\PDO::FETCH_ASSOC)):'';           
                    return empty($result)?$result:$result;
                } catch (\Throwable $th) {
                    throw $th;
                    echo 'somthing went wrong';
                }
            }
        }