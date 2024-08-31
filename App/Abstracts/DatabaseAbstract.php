<?php
namespace App\Abstracts;

use Core\Application;
use Core\Database;
use Core\DB;

abstract class DatabaseAbstract
{
    protected static DB $db;
    public function __construct(){
        $this::$db = Application::db();
    }

}